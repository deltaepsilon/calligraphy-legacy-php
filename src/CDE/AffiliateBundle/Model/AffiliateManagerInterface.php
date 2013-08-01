<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 8/25/12
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CDE\AffiliateBundle\Model;

interface AffiliateManagerInterface
{
	/**
	 * @return AffiliateInterface
	 */
	public function create();

	/**
	 * Persists new affiliate to database
	 */
	public function add(AffiliateInterface $affiliate);

	/**
	 * Updates affiliate to database
	 */
	public function update(AffiliateInterface $affiliate);

	/**
	 * Deletes affiliate from database
	 */
	public function remove(AffiliateInterface $affiliate);

	/**
	 * Finds an affiliate record by ID
	 */
	public function find($id = null);

    /**
     * Paginates affiliates
     */
    public function findByPage($page = 1, $limit = 10);

	/**
	 * Finds an affiliate record by IP
	 */
	public function findByIp($ip);
}
