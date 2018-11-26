<?php

namespace App\Service;

class EntityTableHelper {
    public const COL_DATE = "date";
    public const COL_NUM = "num";
    public const COL_NUM_FMT = "num_fmt";
    public const COL_HTML_NUM = "html_num";
    public const COL_HTML_NUM_FMT = "html_num_fmt";
    public const COL_HTML = "html";
    public const COL_STRING = "string";
	private $header = [];
	private $table = [];
	private $router;
	private $columnType = [];
	private $title;
	private $btn = [];

	public function __construct(\Symfony\Component\Routing\RouterInterface $router) {
		$this->router = $router;
	}

	public function addButton(string $name, string $path): EntityTableHelper {
	    $this->btn[] = [
	        "name" => $name,
            "path" => $this->router->getRouteCollection()->get($path)->getPath()
        ];
	    return $this;
    }

	public function addRow(int $index,array $row) {
		$this->table[$index] = $row;
	}

	/**
	 * @param mixed $title
	 * @return EntityTableHelper
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @param array $header
	 * @return EntityTableHelper
	 */
	public function setHeader(array $header): EntityTableHelper {
		$this->header = $header;
		return $this;
	}

	public function setColumnType(int $index, $type) {
        $this->columnType[$index] = $type;
    }

	public function compile(): array {
	    $column = [];
	    for ($i = 0; $i < count($this->header); $i++) {
	        $attr = [];
	        if (!empty($this->columnType[$i])) {
	            $attr["type"] = $this->columnType[$i];
            }
            if ($attr) {
                $column[] = $attr;
            } else {
                $column[] = null;
            }
        }
		return [
			"title" => $this->title,
            "column" => json_encode($column),
            "btn" => json_encode($this->btn),
			"table" => $this->table,
			"header" => $this->header,
		];
	}
}