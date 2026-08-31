<?php // (C) Copyright Bobbing Wide 

/**
 * Find before e.g. `href="mailto:`
 * Find after e.g. `" title="`
 * If end position after start position
 * then concatenate parts with the replace string in the middle.
 *
 *
 */
 
class Tests_replace_antispambot extends BW_UnitTestCase {

	
	function test_replace_antispambot() {
		$actual = array();
		$actual[] = '<span class="email">e-mail: <a href="mailto:%68e%72&#98;_&#109;i%6c%6c&#101;%72&#64;b&#116;in&#116;e&#114;&#110;&#101;t.&#99;om" title="Send email to: herb_miller@btinternet.com">herb_miller@btinternet.com</a>';
		$actual[] = '<span class="email">e-mail: <a href="mailto:her&#98;%5fmi%6cle%72&#64;&#98;&#116;%69&#110;t&#101;&#114;ne&#116;.%63&#111;%6d" title="Send email to: herb_miller@btinternet.com">herb_miller@btinternet.com</a>';
		$actual = $this->replace_antispambot( $actual );
		$expected = array();
		$expected[] = '<span class="email">e-mail: <a href="mailto:email@example.com" title="Send email to: herb_miller@btinternet.com">herb_miller@btinternet.com</a>';
		$expected[] = '<span class="email">e-mail: <a href="mailto:email@example.com" title="Send email to: herb_miller@btinternet.com">herb_miller@btinternet.com</a>';
		$this->assertEquals( $expected, $actual );
	}
	
}
