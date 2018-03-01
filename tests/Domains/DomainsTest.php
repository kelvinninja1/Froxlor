<?php
use PHPUnit\Framework\TestCase;

/**
 * @covers ApiCommand
 * @covers SubDomains
 * @covers Domains
 */
class DomainsTest extends TestCase
{
	public function testAdminDomainsAdd()
	{
		global $admin_userdata;
		// get customer
		$json_result = Customers::getLocal($admin_userdata, array(
			'loginname' => 'test1'
		))->get();
		$customer_userdata = json_decode($json_result, true)['data'];
		$data = [
			'domain' => 'test.local',
			'customerid' => $customer_userdata['customerid']
		];
		$json_result = Domains::getLocal($admin_userdata, $data)->add();
		$result = json_decode($json_result, true)['data'];
		$this->assertEquals($customer_userdata['documentroot'].'test.local/', $result['documentroot']);
	}
	
	/**
	 * @depends testAdminDomainsAdd
	 */
	public function testAdminDomainsList()
	{
		global $admin_userdata;
		$json_result = Domains::getLocal($admin_userdata)->list();
		$result = json_decode($json_result, true)['data'];
		$this->assertEquals(1, $result['count']);
		$this->assertEquals('test.local', $result['list'][0]['domain']);
	}

	public function testAdminDomainsAddSysHostname()
	{
		global $admin_userdata;
		$data = [
			'domain' => 'dev.froxlor.org',
			'customerid' => 1
		];
		$this->expectExceptionMessage('The server-hostname cannot be used as customer-domain.');
		$json_result = Domains::getLocal($admin_userdata, $data)->add();
	}
	
	public function testAdminDomainsAddNoPunycode()
	{
		global $admin_userdata;
		$data = [
			'domain' => 'xn--asdasd.tld',
			'customerid' => 1
		];
		$this->expectExceptionMessage('You must not specify punycode (IDNA). The domain will automatically be converted');
		$json_result = Domains::getLocal($admin_userdata, $data)->add();
	}

	public function testAdminDomainsAddInvalidDomain()
	{
		global $admin_userdata;
		$data = [
			'domain' => 'dom?*ain.tld',
			'customerid' => 1
		];
		$this->expectExceptionMessage("Wrong Input in Field 'Domain'");
		$json_result = Domains::getLocal($admin_userdata, $data)->add();
	}
	
	/**
	 * @depends testAdminDomainsList
	 */
	public function testAdminDomainsDelete()
	{
		global $admin_userdata;
		$data = [
			'domainname' => 'test.local',
			'delete_mainsubdomains' => 1
		];
		$json_result = Domains::getLocal($admin_userdata, $data)->delete();
		$result = json_decode($json_result, true)['data'];
		$this->assertEquals('test.local', $result['domain']);
	}
}