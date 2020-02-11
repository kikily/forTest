<?php

class xxxx
{
	protected $getLottery;

	public function __construct(Lottery $getLottery)
    {
        $this->getLottery = $getLottery;
    }

	public function getWinningNumber()
	{
		// 綁定號源
		Container::bind('api1', function() {
			return new Api1($this->getLottery);
		});
		Container::bind('api2', function() {
			return new Api2($this->getLottery);
		});
		
		switch ($this->getLottery->gameId) {
			case 1:
				$winningNumber = Container::make('api1');
				break;
			case 2:
				$winningNumber = Container::make('api2');
				break;
			default:
				$winningNumber = Container::make('api1');
				break;
		}
		
		return $winningNumber->getNumber();
	}
}

class Api1
{
	protected $gameId;
	protected $issue;

	public function __construct(Lottery $lottery)
	{
		$this->gameId = $lottery->gameId;
		$this->issue = $lottery->issue;
	}

	public function getNumber()
	{
		switch ($this->gameId) {
			case 1:
				$gamekey='ssc';
				break;
			case 2:
				$gamekey='bjsyxw';
			default:
				$gamekey='ssc';
				break;
		}
		try {
			// 使用GuzzleHttp呼叫API
			$client = new \GuzzleHttp\Client();
			$result = $client->request('GET', 'http://one.fake/v1?gamekey={$gamekey}&issue={$this->issue}');
			$result_arr = json_decode($result->getBody(),true);

			// 取得中獎號碼
			if (!empty($result_arr) && $result_arr['errorCode'] == 0){
				if ($result_arr['result']['data']['gid'] == $this->issue){
					$getWinningNumber = $result_arr['result']['data']['award'];
				} else {
					$getWinningNumber = 'There are some errors.';
				}
			} else {
				$getWinningNumber = 'There are some errors.';
			}
			
			return $getWinningNumber;

		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}

class Api2
{
	protected $gameId;
	protected $issue;

	public function __construct(Lottery $lottery)
	{
		$this->gameId = $lottery->gameId;
		$this->issue = $lottery->issue;
	}

	public function getNumber()
    {
    	switch ($this->gameId) {
			case 1:
				$code='cqssc';
				break;
			case 2:
				$code='bj11x5';
			default:
				$code='bj11x5';
				break;
		}
        try {
        	// 使用GuzzleHttp呼叫API
			$client = new \GuzzleHttp\Client();
			$result = $client->request('GET', 'https://two.fake/newly.do?code={$code}');
			$result_arr = json_decode($result->getBody(),true);

			foreach($result_arr['data'] as $key => $val)
	        {
	        	if ($result_arr['code'] == $code && $this->issue == $val['expect']){
	        		return $val['opencode'];
	        	} else {
	        		return 'There are some errors.';
	        	}
	        }
        } catch (Exception $e) {
        	echo $e->getMessage();
        }
    }
}

class Container
{
	static $register = [];

	static function bind($name, closure $closure)
	{
		self::$register[$name] = $closure;
	}

	static function make($name)
	{
		$closure = self::$register[$name];
		return $closure();
	}
}

?>