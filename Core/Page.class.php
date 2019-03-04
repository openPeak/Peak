<?php
namespace Peak\Core;

/**
 * 分页工具类
 */
class Page {
	// 数据表中总记录数
	private $total; 
	// 每页显示行数
	private $listRows;
	// 拼装后的limit语句
	private $limit;
	// 当前页码
	private $uri;
	// 总页数
	private $pageNum;
	// 页码字符串拼接配置
	private $config  = array('header'=>"个记录", "prev"=>"上一页", "next"=>"下一页", "first"=>"首 页", "last"=>"尾 页");
	// 页码列表显示个数
	private $listNum = 8;

	/**
	 * 构造方法
	 * @param integer $total    总记录数
	 * @param integer $listRows 每页显示多少条记录
	 */
	public function __construct($total, $listRows=10, $pa=""){
		// 总记录数
		$this->total    = $total;
		// 每页显示条数
		$this->listRows = $listRows;
		// 当前唯一请求地址
		$this->uri      = $this->getUri($pa);
		// 当前页码
		$this->page     = !empty($_GET["page"]) ? $_GET["page"] : 1;
		// 总页数
		$this->pageNum  = ceil($this->total/$this->listRows);
		// 拼装limit语句
		$this->limit    = $this->setLimit();
	}

	/**
	 * 拼接limit语句
	 */
	private function setLimit(){
		return "Limit ".($this->page-1)*$this->listRows.", {$this->listRows}";
	}

	/**
	 * 当前请求唯一的地址
	 * @return string    当前地址
	 */
	private function getUri($pa){
		$url   = $_SERVER["REQUEST_URI"].(strpos($_SERVER["REQUEST_URI"], '?')?'':"?").$pa;
		$parse = parse_url($url);

		if(isset($parse["query"])){
			parse_str($parse['query'],$params);
			unset($params["page"]);
			$url = $parse['path'].'?'.http_build_query($params);	
		}

		return $url;
	}

	/**
	 * 魔术方法，调用成员属性
	 */
	public function __get($args){
		return $this->$args;
	}

	/**
	 * 当前页从第几条记录开始
	 * @return int 第几条记录开始
	 */
	private function start(){
		if($this->total == 0)
			return 0;
		else
			return ($this->page-1)*$this->listRows+1;
	}

	/**
	 * 当前页到第几条记录结束
	 * @return int 第几条记录结束
	 */
	private function end(){
		return min($this->page*$this->listRows,$this->total);
	}

	/**
	 * 首页超链接
	 * @return string 首页超链接
	 */
	private function first(){
        $html = "";
		if($this->page == 1)
			$html.= '';
		else
			// $html.= "<li><a href='{$this->uri}&page=1'>{$this->config["first"]}</a></li>";
			$html.= "<li><a href='javascript:getPage(\"{$this->uri}&page=1\")'>{$this->config["first"]}</a></li>";
		return $html;
	}

	/**
	 * 上一页超链接
	 * @return string 上一页超链接
	 */
	private function prev(){
        $html = "";
		if($this->page == 1)
			$html.= '';
		else
			// $html.= "<li><a href='{$this->uri}&page=".($this->page-1)."'>&laquo;{$this->config["prev"]}</a></li>";
			$html.= "<li><a href='javascript:getPage(\"{$this->uri}&page=".($this->page-1)."\")'>&laquo;{$this->config["prev"]}</a></li>";
		return $html;
	}

	/**
	 * 页码列表超链接
	 * @return string  页码列表超链接
	 */
	private function pageList(){
		$linkPage = "";
		$inum     = floor($this->listNum/2);
		for($i = $inum;$i >= 1;$i--){
			$page = $this->page-$i;
			if($page < 1)
				continue;
			// $linkPage.= "<li><a href='{$this->uri}&page={$page}'>{$page}</a></li>";
			$linkPage.= "<li><a href='javascript:getPage(\"{$this->uri}&page={$page}\")'>{$page}</a></li>";
		}
		$linkPage.= "<li class=\"alclick\"><a href=\"#\">{$this->page}</a></li>";

		for($i = 1;$i <= $inum;$i++){
			$page = $this->page+$i;
			if($page <= $this->pageNum){
				// $linkPage.= "<li><a href='{$this->uri}&page={$page}'>{$page}</a></li>";
				$linkPage.= "<li><a href='javascript:getPage(\"{$this->uri}&page={$page}\")'>{$page}</a></li>";
			}else{
				break;
			}
		}
		return $linkPage;
	}

	/**
	 * 下一页超链接
	 * @return string 页码超级链接
	 */
	private function next(){
        $html = "";
		if($this->page == $this->pageNum){
			$html.= '';
		}else{
			$html.= "<li><a href='javascript:getPage(\"{$this->uri}&page=".($this->page+1)."\")'>{$this->config["next"]}&raquo;</a></li>";
		}
		return $html;
	}

	/**
	 * 尾页超链接
	 * @return string 页码超级链接
	 */
	private function last(){
        $html = "";
		if($this->page == $this->pageNum){
			$html.= '';
		}else{
			$html.= "<li><a href='javascript:getPage(\"{$this->uri}&page=".($this->pageNum)."\")'>{$this->config["last"]}</a></li>";
		}
		return $html;
	}

	/**
	 * 手动跳转输入页码跳转
	 * @return string 跳转超级链接
	 */
	private function goPage(){
		return '<input type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>'.$this->pageNum.')?'.$this->pageNum.':this.value;location=\''.$this->uri.'&page=\'+page+\'\'}" value="'.$this->page.'" style="width:25px"><input type="button" value="GO" onclick="javascript:var page=(this.previousSibling.value>'.$this->pageNum.')?'.$this->pageNum.':this.previousSibling.value;location=\''.$this->uri.'&page=\'+page+\'\'">';
	}

	/**
	 * 全部页码列表字符串
	 * @param  array  $display 所需要的字符串参数选择
	 * @return string          显示的页码列表字符串
	 */
	public function fpage($display=array(3,4,5,6,7)){
		// $html[0] = "&nbsp;&nbsp;共有<b>{$this->total}</b>{$this->config["header"]}&nbsp;&nbsp;";
		// $html[1] = "&nbsp;&nbsp;每页显示<b>".($this->end()-$this->start()+1)."</b>条，本页<b>{$this->start()}-{$this->end()}</b>条&nbsp;&nbsp;";
		// $html[2] = "&nbsp;&nbsp;<b>{$this->page}/{$this->pageNum}</b>页&nbsp;&nbsp;";
		$html[3] = $this->first();
		$html[4] = $this->prev();
		$html[5] = $this->pageList();
		$html[6] = $this->next();
		$html[7] = $this->last();
		//$html[8] = $this->goPage();
		$fpage   = '';
		foreach($display as $index){
			$fpage.= $html[$index];
		}
		return $fpage;
	}
}
