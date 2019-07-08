<?php
//拉链法解决冲突
include("./BaseObj.php");
class HashTableLink implements BaseObj,ArrayAccess{
	public $size;
	public $buckets;
	public $used = 0;
	private $load_factor;//负载因子

	public function __construct($size = 33){
		$this->buckets = new SplFixedArray($size);
		for($i=0;$i<$size;$i++){
			$this->buckets[$i] = new LinkedList();
		}
		$this->size = $size;
	}

	public function hashing($key){
		$hash = crc32($key)%$this->size;
		if($hash<0){//注意不能为负，因为SplFixedArray不能插入负
			return $hash+$this->size;
		}
		return $hash;
	}

	public function insertHash($key,$val){
		$hash = $this->hashing($key);
		$res = $this->buckets[$hash]->insert($key,$val);
		if($res){
			$this->used++;
		}
	}

	public function deleteHash($key){
		$hash = $this->hashing($key);
		$res = $this->buckets[$hash]->delete($key);
		if($res){
			$this->used--;
		}
	}

	public function issetHash($key){
    	$index = $this->hashing($key);
        return $this->buckets[$index]->isset($key);
    }

    public function findHash($key){
        $index = $this->hashing($key);
        return $this->buckets[$index]->find($key);
    }

	public function displayHashtable(){
		for($i=0;$i<$this->size;$i++){
			$res = $this->buckets[$i]->display();
			var_dump($res);
		}
	}

	//获取负载因子
    public function getLoadFactor(){
        return number_format($this->used/$this->size,2);
    }

    public function offsetExists($key){
    	return $this->issetHash($key);
    }

    public function offsetGet($key){
    	return $this->findHash($key);
    }

    public function offsetSet($key,$val){
    	return $this->insertHash($key,$val);
    }

    public function offsetUnset($key){
    	return $this->deleteHash($key);
    }
}
class LinkedList{
	public $head;
	public $size = 0;
	public function insert($key,$val){
		$node = new Node($key,$val);
		if($this->head == null){
			$this->head = $node;
		}else{
			//需要遍历链表，防止插入相同的键
			$current = $this->head;
			while($current!=null){
				if($current->key == $key){
					$current->val = $val;
					return false;
				}
				$current = $current->next;
			}
			$node->next = $this->head;
			$this->head = $node;
		}
		$this->size++;
		return true;
	}

	public function find($key){
		$current = $this->head;
		while($current!=null){
			if($current->key == $key){
				return $current->val;
			}
			$current = $current->next;
		}
		return false;
	}

	public function delete($key){
		if($this->size == 0){
			return false;
		}else{
			$hasDelete = false;
			$current = $this->head;
			if($current->key == $key){
				$this->head = $current->next;
				$hasDelete = true;
			}else{
				while($current->next!=null){
					if($current->next->key == $key){
						$current->next = $current->next->next;
						$hasDelete = true;
						continue;
					}
					$current = $current->next;
				}
			}
			if($hasDelete){
				$this->size--;
				return true;
			}
			return false;
		}
	}

	public function isset($key){
		if($this->size == 0){
			return false;
		}else{
			$current = $this->head;
			if($current->key == $key){
				return true;
			}else{
				while($current->next!=null){
					if($current->next->key == $key){
						return true;
					}
					$current = $current->next;
				}
			}
			return false;
		}
	}

	public function display(){
		$res = "";
		$tmp = $this->head;
		while($tmp!=null){
			$res.="{$tmp->val}->";
			$tmp=$tmp->next;
		}
		$res = rtrim($res,"->");
		return $res;
	}
}
class Node{
	public $key;
	public $val;
	public $next;
	public function __construct($key,$val){
		$this->key = $key;
		$this->val = $val;
	}
}

$obj = new HashTableLink();
$obj["a"] = 123;
$obj["b"] = 2;
$obj["c"] = "c";
$obj["a"] = "88";
unset($obj["b"]);
for($i=0;$i<30000;$i++){
	$obj[mt_rand(-10000,10000)] = mt_rand(-10000,10000);
}
$obj->displayHashtable();