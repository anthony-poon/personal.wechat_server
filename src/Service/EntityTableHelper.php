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
	private $addPath = "";
	private $editPath = "";
	private $delPath = "";
	private $router;
	private $columnType = [];
	private $title;

	public function __construct(\Symfony\Component\Routing\RouterInterface $router) {
		$this->router = $router;
	}

	/**
	 * @param string $addPath
	 * @return EntityTableHelper
	 */
	public function setAddPath(string $addPath): EntityTableHelper {
		$this->addPath = $this->router->getRouteCollection()->get($addPath)->getPath();
		return $this;
	}

	/**
	 * @param string $editPath
	 * @return EntityTableHelper
	 */
	public function setEditPath(string $editPath): EntityTableHelper {
		$this->editPath = $this->router->getRouteCollection()->get($editPath)->getPath();
		return $this;
	}

	/**
	 * @param string $delPath
	 * @return EntityTableHelper
	 */
	public function setDelPath(string $delPath): EntityTableHelper {
		$this->delPath = $this->router->getRouteCollection()->get($delPath)->getPath();
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
			"table" => $this->table,
			"header" => $this->header,
			"addPath" => $this->addPath,
			"delPath" => $this->delPath,
			"editPath" => $this->editPath
		];
	}
}