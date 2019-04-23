<?php
/**
 * Make asynchronous requests to different resources as fast as possible and process the results as they are ready.
 */
class Requests
{
	public $handle;

	public function __construct()
	{
		$this->handle = curl_multi_init();
	}

	public function process($urls_array, $headers_array, $params_array, $callback)
	{
		$ind = 0;
		$nb_urls = count($urls_array);
		
		for($ind=0;$ind<$nb_urls;$ind++)
		{
			$url = $urls_array[$ind];
			$params = $params_array[$ind];
			$headers = $headers_array[$ind];
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	# Return response instead of printing.
			curl_multi_add_handle($this->handle, $ch);
		}

		do {
			$mrc = curl_multi_exec($this->handle, $active);

			if ($state = curl_multi_info_read($this->handle))
			{
				$info = curl_getinfo($state['handle']);
				
				$callback(curl_multi_getcontent($state['handle']), $info);
				curl_multi_remove_handle($this->handle, $state['handle']);
			}

			usleep(10000); // stop wasting CPU cycles and rest for a couple ms

		} while ($mrc == CURLM_CALL_MULTI_PERFORM || $active);

	}

	public function __destruct()
	{
		curl_multi_close($this->handle);
	}
}