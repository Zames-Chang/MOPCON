<?php

class UnconfControllerTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testGetUnconfAPI($env)
    {
        putenv('APP_ENV=' . $env);
        /** action **/
        $response = $this->call('GET', '/api/2019/unconf');
        $result = json_decode($response->getContent(), true);
        /** assert **/
        $this->assertEquals(true, $result['success']);
        if($env == 'develop'){
            $this->assertTrue(array_key_exists('1571414400', $result['data']));
            $this->assertTrue(array_key_exists('1571500800', $result['data']));
        }
    }
    public function provider()
    {
        return [
            [
                'production',
            ],
            [
                'develop',
            ],
        ];
    }
}
