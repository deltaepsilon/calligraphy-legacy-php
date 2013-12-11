<?php

namespace CDE\UtilityBundle\Manager;

class AWSManager
{
    protected $container;
    protected $cloudFront;
    protected $s3;
    protected $ec2;
    
    public function __construct($container){
        $this->container = $container;
        $this->cloudFront = $this->container->get('aws_cloud_front');
        $this->s3 = $this->container->get('aws_s3');
        $this->ec2 = $this->container->get('aws_ec2');
    }
    
        
    protected function getDistroId()
    {
        return $this->container->getParameter('aws_distribution_id');
    }

    public function getDistroInfo()
    {
        $distroId = $this->getDistroId();
        $response = $this->cloudFront->get_distribution_info($distroId);
        return $response;
    }
    
    protected function getDistroConfig()
    {
        $distroId = $this->getDistroId();
        $response = $this->cloudFront->get_distribution_config($distroId);
        return $response;
    }

    protected function getRedis() {
        return $this->container->get('snc_redis.default');
    }

    public function setRsa()
    {
        $fingerprint = $this->container->getParameter('aws_rsa_key_pair_id');
        $fingerprint_response = $this->cloudFront->set_keypair_id($fingerprint);
        $private_key_path = $this->container->getParameter('aws_rsa_key');
        $private_key = file_get_contents($private_key_path);
        $key_response = $this->cloudFront->set_private_key($private_key);
    }
    
    public function getSignedUri($uri, $days = 1, $filename = null) {
        if (!is_int($days) || $days <= 0) {
            $days = 1;
        }
        $expires = new \DateTime('+'.$days.' day');

        if (!isset($filename)) {
            preg_match('/[^\/]+?$/', $uri, $filenameMatches);
            $filename = $filenameMatches[0];
        }

        $redis = $this->getRedis();
        $privateUri = $redis->get($filename);
        if (!isset($privateUri)) {
            $distroInfo = $this->getDistroInfo();
            $distribution_hostname = $distroInfo->body->DomainName;
            $this->setRsa();
            $privateUri = $this->cloudFront->get_private_object_url($distribution_hostname, $filename, $expires->getTimestamp());

            $this->getRedis()->set($filename, $privateUri);
            $this->getRedis()->expire($filename, $days * 86400);

            if (!$privateUri) {
                $privateUri = $uri;
            }

        }

        return $privateUri;

    }
    
    public function getSignedUriByFilename($uri, $days = 1) {
        return $this->getSignedUri($uri, $days, $uri);

    }    
    
    public function secureDistribution()
    {
        $distroInfo = $this->getDistroInfo();
        $distroId = $this->getDistroId($distroInfo->body->DomainName);
        $distroConfig = $this->getDistroConfig();
        $etag = $distroConfig->header['etag'];
        
        $s3_bucket_name = $distroConfig->body->CNAME;
        $s3_acl = $this->s3->get_bucket_acl($s3_bucket_name);
        $owner_id = $s3_acl->body->Owner->ID;
        $callerReference = $distroInfo->body->DistributionConfig->CallerReference;
        
        $response_oai = $this->cloudFront->create_oai($callerReference);
        
        // Update config with OAI and TrustedSigners
        $oai_id = $response_oai->body->Id;
        $s3_canonical_id = $response_oai->body->S3CanonicalUserId;
        $cf_updated_config = $this->cloudFront->update_config_xml($distroConfig, array(
            'OriginAccessIdentity' => $oai_id,
            'TrustedSigners' => array('Self'),
        ));

        // Set the config
        $response_cf = $this->cloudFront->set_distribution_config(
            $distroId,
            $cf_updated_config,
            $etag
        );
        
        // Grant S3 read permission to CloudFront distro
        $response_s3 = $this->s3->set_bucket_acl($s3_bucket_name, array(
            array('id' => $owner_id, 'permission' => 'FULL_CONTROL' ),
            array('id' => $s3_canonical_id, 'permission' => 'READ' ),
        ));
        if (!$response_s3->isOk()) {
            return FALSE;
        }

        //Set permissions on all s3 objects in the top level
        $list = $this->s3->list_objects($s3_bucket_name);
        foreach ($list->body->Contents as $object) {
            $key = (string) $object->Key;
            $this->s3->set_object_acl($s3_bucket_name, $key, array(
                array('id' => $owner_id, 'permission' => 'FULL_CONTROL' ),
                array('id' => $s3_canonical_id, 'permission' => 'READ' ),
            ));
            $this->s3->get_object_acl($s3_bucket_name, $key);
        }

        $oai_list = $this->cloudFront->get_oai_list();
        
        return array(
            'oaiId' => $oai_id,
            'oaiList' => $oai_list,
            's3CanonicalId' => $s3_canonical_id,
            'cfResponse' => $response_cf,
            's3Response' => $response_s3->header,
            );
    }
    
    public function getGalleryManifest($name) {
        $aws_public = $this->container->getParameter('aws_public');
        $distroInfo = $this->cloudFront->get_distribution_info($aws_public['distribution_id']);
        $domain = $distroInfo->body->DistributionConfig->CNAME;
        $folder = $aws_public['gallery_folder'];
        $base = $folder.$name;
        $list = $this->s3->list_objects($domain, array(
            'prefix' => $base,
        ));
        $contents = $list->body->Contents;
        $result = array('base' => 'http://'.$domain, 'contents' => array());
        foreach ($contents as $object) {
            $string = $object->Key;
            $result['contents'][] = "$string";
        }
        // The first element of the array should always be the base folder
        array_splice($result['contents'], 0, 1);
        return $result;
    }
    
    public function signPageUrls($page)
    {
        $html = $page->getHtml();
        $linkCount = preg_match_all('/{{.+>?}}/', $html, $linksDirty);
        if ($linkCount) {
			$links = preg_replace('/({|}| )/', '', $linksDirty[0]);
			$signedUris = array();
			foreach ($links as $link) {
				$signedUris[$link] = $this->getSignedUri($link);
			}
			// Escape old url for regex and replace the old with the new
			// Clean out the extra spaces and brackets as well
			foreach ($signedUris as $oldDirty => $new) {
				$old = preg_replace('/\//', '\/', $oldDirty);
				$html = preg_replace('/{+?\s+?'.$old.'\s+?}+/', $new, $html);
			}
        }
        return $page->setSignedHtml($html);
    }
    
    public function copyGalleryFile($filename, $folder = null)
    {
        if (!isset($folder)) {
            $folder = __DIR__.'/../../../../web/';
        }

        $source = $folder.$filename;
        $bucketname = $this->getBucketName();
        $file = fopen($source, 'r');
        $responseCreate = $this->s3->create_object($bucketname, $filename, array(
            'fileUpload' => $file,
            'storage' => 'REDUCED_REDUNDANCY',
            'headers' => array(
                'Cache-Control' => "max-age=2052000",
            ),
        ));
        $this->secureS3Object($filename);
        fclose($file);
        unlink($source);
    }
    
    public function deleteGalleryFile($filename)
    {
        $distroConfig = $this->getDistroConfig();
        $bucketname = (string)$distroConfig->body->CNAME;
        $response = $this->s3->delete_object($bucketname, $filename);
        return $response;
    }

    public function getBucketName() {
        $distroConfig = $this->getDistroConfig();
        return (string)$distroConfig->body->CNAME;
    }

    public function getOai() {
        $oai_list = $this->cloudFront->get_oai_list();
        return $oai_list[0];
    }

    public function getS3CanonicalId($oai) {
        $id = (string) $this->cloudFront->get_oai($oai)->body->S3CanonicalUserId;
        return $id;
    }

    public function getOwnerId($bucket) {
        $s3_acl = $this->s3->get_bucket_acl($bucket);
        return $s3_acl->body->Owner->ID;
    }

    public function secureS3Object($object_name) {
        $bucket = $this->getBucketName();
        $owner = $this->getOwnerId($bucket);
        $s3 = $this->getS3CanonicalId($this->getOai());
        $result = $this->s3->set_object_acl($bucket, $object_name, array(
            array('id' => $owner, 'permission' => 'FULL_CONTROL' ),
            array('id' => $s3, 'permission' => 'READ' ),
        ));
        return $result;
    }

    public function listImages($prefix) {
        $bucket = $this->container->getParameter('aws_images_bucket');
        $path = $this->container->getParameter('aws_images_path');
        $prefix = preg_replace('/\|/', '/', $prefix);
        $response = $this->s3->list_objects($bucket, array('prefix' => $prefix));
        $result = array();
        foreach($response->body->Contents as $object) {
            $key = (String) $object->Key;
            if ($key !== $prefix && $key !== $prefix.'/') {
                $result[] = $path.'/'.$key;
            }
        }
        return $result;
    }

    public function listPrivateFiles() {
        $bucket = $this->container->getParameter('aws_private_bucket');
        $response = $this->s3->list_objects($bucket, array('delimiter' => '/'));
        $result = array();
        foreach($response->body->Contents as $object) {
            $key = (String) $object->Key;
            $result[] = $key;
        }
        return $result;
    }

    public function setCFPermissions($prefix) {
        $bucket = $this->container->getParameter('aws_private_bucket');
        $owner = $this->getOwnerId($bucket);
        $s3 = $this->getS3CanonicalId($this->getOai());
        $result = $this->s3->set_object_acl($bucket, $prefix, array(
            array('id' => $owner, 'permission' => 'FULL_CONTROL' ),
            array('id' => $s3, 'permission' => 'READ' ),
        ));
        return $result;
    }
}
