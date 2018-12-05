<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace tests;

class ExampleTest extends TestCase
{

//    public function testBasicExample()
//    {
//        $this->visit('/')->see('ThinkPHP');
//    }
    /**
     * @test
     * @dataProvider isValidFileName_Provider
     * 注意，尽量使得测试的方法名称有意义，这非常重要，便于维护测试代码。有规律
     */
    public function isValidFileName_VariousExtensions_ChecksThem($filename, $boo)
    {
        $analyzer = new \app\index\controller\index();
        $result = $analyzer->isValidLogFileName($filename);
        $this->assertEquals($result, $boo);
    }

    public function isValidFileName_Provider()
    {
        return array(
            array("file_with_bad_extension.foo", false),
            array("file_with_good_extension.slf", true),
            array("file_with_good_extension.SLF", true),
        );
    }


}