<?php

/**
 * 字符串->文件下载
 * dataUrl方式，不通过临时文件，无临时文件磁盘io操作
 * 解决发送生成zip文件等必须通过临时文件的方案（php的zip等相关的流只读）
 * 仅适用于小文件下载
 * 大文件需要手动php进行zip编码~
 * @author xiaofeng
 *
 * example
 *
 * String2Files::sendOne("helloWorld", "test.txt");
 *
 * ajax:
 * String2Files::sendOne("helloWorld", "test.txt", false);
 * ajaxCallback(result) {eval(result);}
 *
 * $s2f = new String2Files;
 * for ($n=0; $n < 2; $n++) {
 * 	$str = "<?php\necho '你好{$n}';";
 * 	$s2f->attatch($str, "f{$n}.php", "text/plain", ["charset=utf-8"]);
 * 	$s2f->attatchZip($str, "f{$n}.php.zip");
 * }
 * $s2f->send();
 *
 */
class String2Files {
	private $files = [];

	/**
	 * helper method
	 * @param  string  $str       [description]
	 * @param  string  $fileName  [description]
	 * @param  boolean $zip       [description]
	 * @param  boolean $scriptTag [description]
	 * @return null
	 */
	public static function sendOne($str, $fileName, $zip = true, $scriptTag = true) {
		$s2f = new self;
		if($zip) {
			$s2f->attatchZip($str, $fileName);
		} else {
			$s2f->attatch($str, $fileName, "text/plain", ["charset=utf-8"]);
		}
		$s2f->send($scriptTag);
	}

	public function attatch($content, $fileName, $mimeType = 'text/plain', array $option = []) {
		array_unshift($option, $mimeType);
		array_push($option, "base64," . base64_encode($content));
		$this->files[] = [ $fileName, "data://" . implode(';', $option) ];
	}

	public function attatchZip($content, $fileName) {
		if(strcasecmp(substr($fileName, -strlen(".zip")), ".zip") !== 0) {
			$fileName .= ".zip";
		}
		$this->attatch(gzencode($content, 9), $fileName, "application/zip");
	}

	/**
	 * 发送文件
	 * @param  boolean $scriptTag 是否添加script标签，用于ajax请求
	 * @return null
	 */
	public function send($scriptTag = true) {
		if(!$this->files) {
			return;
		}

		if($scriptTag) {
			echo "<script>";
		}

		// 多文件下载需要允许浏览器自动下载多个文件
		// 需要浏览器支持 download属性
		echo <<<JS
(function(){
var download = function (dataUrl, fileName) {
  var link = document.createElement("a");
  link.download = fileName;
  link.href = dataUrl;
  link.click();
};
JS;
		if(count($this->files) > 1) {
			echo 'alert("请允许浏览器自动下载多个文件！");';
		}
		foreach($this->files as list($fileName, $data)) {
			echo "download('{$data}', '{$fileName}');";
		}
		echo "}());";
		if($scriptTag) {
			echo "</script>";
		}
	}
}
