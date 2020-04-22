<?php

class ZoneFile {
	private $domain = 'example.com.';
	private $ttl = 60; // The default TTL, used when one is not specified for a record
	private $records = [];
	private $strlen_maxes = [
		'name' => 0,
		'ttl' => 0,
		'class' => 0,
		'type' => 0
	];
	private $spf = [
		'params' => NULL,
		'_includes' => []
	];


	public function __construct($domain=NULL, $ttl=NULL){
		if( !is_null($domain) ) $this->domain = $domain;
		if( !is_null($ttl) ) $this->ttl = $ttl;
	}


	private function addRecord($name, $ttl, $class, $type, $data){
		$this->records[]=[
			'name' => $name,
			'ttl' => $ttl,
			'class' => $class,
			'type' => $type,
			'data' => $data
		];
	}


	// This function searches the zone file for the record type(s) specified and returns true if at least one record with that/those type(s) exists, or false otherwise. $types can be an array with one or more types, or a string with a single type
	private function hasRecordType($types){
		// If $types is not an array, convert it to one
		if( !is_array($types) ) $types = [$types];

		// Prevents case mismatch issues
		$types = array_map(
			function($type){ return strtoupper($type); },
			$types
		);

		// Search the records in the zone file for the type(s) specified
		foreach($this->records as $record){
			// If a match is found, return true
			if( in_array($record['type'], $types) ) return true;
		}

		// Otherwise, return false
		return false;
	}


	private function calculateStrlenMaxes(){
		foreach($this->records as $record){
			foreach(['name', 'ttl', 'class', 'type'] as $key){
				$len = strlen($record[$key]);

				if( $len>$this->strlen_maxes[$key] ){
					$this->strlen_maxes[$key] = $len;
				}
			}
		}
	}


	private function pad($input, $max_len){
		$pad = 1; // The number of extra tabs padding
		$chars_per_tab = 8;

		$max_chars = (
			ceil($max_len/$chars_per_tab) + $pad
		) * $chars_per_tab;

		$tabs_needed = ceil(
			(
				$max_chars-strlen($input)
			)/$chars_per_tab
		);

		return $input . str_repeat("\t", $tabs_needed);
	}


	// Add an A record
	public function addA($name, $ip, $ttl=NULL){
		if( is_null($ttl) ) $ttl = $this->ttl;
		$this->addRecord($name, $ttl, 'IN', 'A', $ip);
	}


	// Add an AAAA record
	public function addAAAA($name, $ip, $ttl=NULL){
		if( is_null($ttl) ) $ttl = $this->ttl;
		$this->addRecord($name, $ttl, 'IN', 'AAAA', $ip);
	}


	// Add a CNAME record
	public function addCname($name, $cname, $ttl=NULL){
		if( is_null($ttl) ) $ttl = $this->ttl;
		$this->addRecord($name, $ttl, 'IN', 'CNAME', $cname);
	}


	// Add a TXT record
	public function addTxt($name, $data, $ttl=NULL){
		if( is_null($ttl) ) $ttl = $this->ttl;
		$this->addRecord($name, $ttl, 'IN', 'TXT', "\"$data\"");
	}


	// Add a MX record
	public function addMx($name, $pri, $server, $ttl=NULL){
		if( is_null($ttl) ) $ttl = $this->ttl;
		$this->addRecord($name, $ttl, 'IN', 'MX', "$pri $server");
	}


	// Add a NS record
	public function addNs($ns, $ttl=NULL){
		if( is_null($ttl) ) $ttl = $this->ttl;
		$this->addRecord($this->domain, $ttl, 'IN', 'NS', $ns);
	}


	public function addFastmail($ttl=NULL){
		// MX records
		$this->addMx($this->domain, 10, 'in1-smtp.messagingengine.com.', $ttl);
		$this->addMx($this->domain, 20, 'in2-smtp.messagingengine.com.', $ttl);

		// DKIM records
		foreach([1, 2, 3] as $i){
			$this->addCname("fm$i._domainkey", "fm$i.{$this->domain}dkim.fmhosted.com.", $ttl);
		}

		// SPF include
		$this->spf['_includes'][]='spf.messagingengine.com';

		// A records
		$this->addA('mail', '66.111.4.147');
		$this->addA('mail', '66.111.4.148');
	}


	public function addMailgun($dkim_hostname=NULL, $dkim_key=NULL, $subdomain='outgoing-mail', $ttl=NULL){
		// MX records
		$this->addMx($subdomain, 10, 'mxa.mailgun.org.', $ttl);
		$this->addMx($subdomain, 10, 'mxb.mailgun.org.', $ttl);

		// CNAME record
		$this->addCname("email.$subdomain", 'mailgun.org.', $ttl);

		// DKIM record
		if(
			!is_null($dkim_hostname)
			&&
			!is_null($dkim_key)
		) $this->addTxt("$dkim_hostname.$subdomain", $dkim_key, $ttl);

		// SPF record/include
		$this->addTxt($subdomain, 'v=spf1 include:mailgun.org ~all', $ttl);
		$this->spf['_includes'][]='mailgun.org';
	}


	/*
	Add a SPF record

	Unlike the other add* functions, addSpf() doesn't immediately add the SPF record to the zone file via $this->records. Instead, it simply stores the params that are sent to it in $this->spf['params']

	The internal helper function addSpfIf() is what actually generates the SPF record and adds it to the zone file. It is automatically called by output()

	Why break things up into two functions like this? Because the SPF record is dynamic, not static, and the other records in the zone file may affect it. So, it's important that the SPF record itself isn't actually generated until all records have been added to the zone file, which is exactly what addSpfIf() does
	*/
	public function addSpf($mx=NULL, $a=NULL, $includes=[], $mode='-all', $ttl=NULL){
		$this->spf['params'] = [
			'mx' => $mx,
			'a' => $a,
			'includes' => $includes,
			'mode' => $mode,
			'ttl' => $ttl
		];
	}


	private function addSpfIf(){
		// If SPF params are not set (because addSpf() was never called), don't add an SPF record to the zone file
		if(
			!isset($this->spf['params'])
			||
			!is_array($this->spf['params'])
		) return;

		// Get SPF variables
		$spf = $this->spf;
		$params = $spf['params'];

		// Get params
		$mx = $params['mx'];
		$a = $params['a'];
		$includes = $params['includes'];
		$mode = $params['mode'];
		$ttl = $params['ttl'];

		// Default values
		if( is_null($mx) ) $mx = $this->hasRecordType('MX');
		if( is_null($a) ) $a = $this->hasRecordType(['A', 'AAAA']);
		if( is_null($mode) ) $mode = '-all';

		// Generate the includes array
		$includes = array_unique(
			array_merge($includes, $spf['_includes'])
		);

		// Convert params to strings for the SPF TXT record
		$mx_str = $mx ? 'mx ':'';
		$a_str = $a ? 'a ':'';
		$includes_str = implode(
			'',
			array_map(
				function($value){ return "include:$value "; },
				$includes
			)
		);

		// Add TXT record
		$this->addTxt($this->domain, "v=spf1 $mx_str$a_str$includes_str$mode", $ttl);
	}


	// Generates the zone file
	public function output(){
		$this->addSpfIf();

		$this->calculateStrlenMaxes();

		$output = <<<OUTPUT
\$ORIGIN {$this->domain}
\$TTL {$this->ttl}
;{$this->domain}

OUTPUT;

		foreach($this->records as $record){
			$output.=$this->pad($record['name'], $this->strlen_maxes['name']);
			$output.=$this->pad($record['ttl'], $this->strlen_maxes['ttl']);
			$output.=$this->pad($record['class'], $this->strlen_maxes['class']);
			$output.=$this->pad($record['type'], $this->strlen_maxes['type']);
			$output.=$record['data'];
			$output.="\n";
		}

		return $output;
	}
}

?>