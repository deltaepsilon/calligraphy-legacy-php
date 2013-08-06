<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 8/25/12
 * Time: 12:54 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\AffiliateBundle\Entity;

use CDE\AffiliateBundle\Model\AffiliateManagerInterface;
use CDE\AffiliateBundle\Model\AffiliateInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AffiliateManager implements AffiliateManagerInterface
{
	protected $em;
	protected $class;
	protected $repo;

	public function __construct(EntityManager $em, $class, $paginator){
		$this->em = $em;
		$this->repo = $this->em->getRepository($class);
		$this->class = $class;
        $this->paginator = $paginator;
	}

	public function create()
	{
		$affiliate = new Affiliate();
		return $affiliate;
	}

	public function add(AffiliateInterface $affiliate)
	{
		$existing = $this->findByIp($affiliate->getIp());
		if (!$existing) {
			$this->cleanSpaces($affiliate);
			$this->em->persist($affiliate);
			$this->em->flush();
		}
	}

	public function update(AffiliateInterface $affiliate)
	{
		$this->cleanSpaces($affiliate);
		$this->em->persist($affiliate);
		$this->em->flush();
	}

	private function cleanSpaces ($affiliate)
	{
		$name = $affiliate->getAffiliate();
		$affiliate->setAffiliate(preg_replace("/\s/", '_', $name));
	}

	public function remove(AffiliateInterface $affiliate)
	{
		$this->em->remove($affiliate);
		$this->em->flush();
	}

	public function find($id = null)
	{
		if ($id) {
			$query = $this->em->createQuery('
				select l, m
				from CDEAffiliateBundle:Affiliate l
				join l.users m
				where l.id = :id
			')->setParameter('id', $id);
		} else {
			$query = $this->em->createQuery('
			select l, m
			from CDEAffiliateBundle:Affiliate l
			join l.users m
		');
		}
		$affiliates = $query->getResult();

		return $affiliates;
	}

    public function findByPage($page = 1, $limit = 10)
    {
        $query = $this->em->createQuery('
            select l, m
			from CDEAffiliateBundle:Affiliate l
			join l.users m
        ');

        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }

	public function findByIp($ip)
	{
		$affiliate = $this->repo->findByIp($ip);
		return $affiliate;
	}

    public function getReport()
    {
        $query = $this->em->createQuery('
            select l.id, l.affiliate, SUBSTRING(n.created, 1, 4) as year, SUBSTRING(n.created, 6, 2) as month, sum(n.amount) as aggregate
            from CDEAffiliateBundle:Affiliate l
            join l.users m
            join m.transactions n
            group by year, month, l.affiliate
            order by l.affiliate, year asc, month asc
        ');

//        select l.affiliate, YEAR(n.created), MONTH(n.created), sum(n.amount) as amount
//            from CDEAffiliateBundle:Affiliate l
//            join CDEUserBundle:User m on l.ip = m.ip
//            join CDECartBundle:transaction n on m.id = n.user_id
//            group by YEAR(n.created), MONTH(n.created), l.affiliate
//            order by l.affiliate, YEAR(n.created) asc, MONTH(n.created) asc

        $affiliates = $query->getResult();

        return $affiliates;
    }

}
