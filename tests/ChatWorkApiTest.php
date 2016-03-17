<?php

require_once("../ChatWorkApi.php");
define('CHAT_WORK_TOKEN', $argv[1]);

class GetTest extends PHPUnit_Framework_TestCase{
    /**
     * @test
     */
    public function _と判定する(){
        $this->assertFalse((new ChatWorkApi(CHAT_WORK_TOKEN))->get());
    }

    /**
     * @test
     */
    public function _と判定する(){
        $this->assertFalse((new ChatWorkApi())->get(100));
    }
}

