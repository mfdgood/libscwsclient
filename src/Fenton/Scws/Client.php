<?php
/**
 * A scws client
 *
 * @version 1.0.0
 * @author fenton ma
 * @package libscwsclient
 */
declare(strict_types = 1);

namespace Fenton\Scws;

use \Exception;

final class Client
{

    /**
     * 返回 scws 版本号名称信息（字符串）。
     *
     * @var int
     */
    const XDICT_TXT = SCWS_XDICT_TXT;

    /**
     * 表示直接读取 xdb 文件（此为默认值）
     *
     * @var int
     */
    const XDICT_XDB = SCWS_XDICT_XDB;

    /**
     * 表示将 xdb 文件全部加载到内存中，以 XTree 结构存放，可用异或结合另外2个使用。
     *
     * @var int
     */
    const XDICT_MEM = SCWS_XDICT_MEM;

    /**
     * 短词
     *
     * @var int
     */
    const MULTI_SHORT = SCWS_MULTI_SHORT;

    /**
     * 二元（将相邻的2个单字组合成一个词）
     *
     * @var int
     */
    const MULTI_DUALITY = SCWS_MULTI_DUALITY;

    /**
     * 重要单字
     *
     * @var int
     */
    const MULTI_ZMAIN = SCWS_MULTI_ZMAIN;

    /**
     * 全部单字
     *
     * @var int
     */
    const MULTI_ZALL = SCWS_MULTI_ZALL;

    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * @var \SimpleCWS
     */
    protected static $scwsObject = null;

    /**
     * @var string
     */
    protected $string = null;

    protected function __construct()
    {
        if (!extension_loaded("scws")) {
            throw new Exception("scws extension didn't loaded!");
        }
        self::$scwsObject = scws_new();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设定分词词典、规则集、欲分文本字符串的字符集。
     *
     * @param string $charset 要新设定的字符集，目前只支持 utf8 和 gbk。（注：默认为 gbk，utf8不要写成utf-8）
     * @return bool
     */
    public function setCharset(string $charset)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->set_charset($charset);
        }
        return false;
    }

    /**
     * 添加分词所用的词典，新加入的优先查找。
     *
     * @param string $dictPath 词典的路径，可以是相对路径或完全路径。（遵循安全模式下的 open_basedir）
     * @param int $mode 可选，表示加载的方式。
     * @return bool
     */
    public function addDict(string $dictPath, int $mode = self::XDICT_XDB)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->add_dict($dictPath, $mode);
        }
        return false;
    }

    /**
     * 设定分词所用的词典并清除已存在的词典列表。
     *
     * @param string $dictPath 词典的路径，可以是相对路径或完全路径。（遵循安全模式下的 open_basedir）
     * @param int $mode 可选，表示加载的方式。
     * @return bool
     */
    public function setDict(string $dictPath, int $mode = self::XDICT_XDB)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->set_dict($dictPath, $mode);
        }
        return false;
    }

    /**
     * 设定分词所用的新词识别规则集（用于人名、地名、数字时间年代等识别）。
     *
     * @param string $rulePath 规则集的路径，可以是相对路径或完全路径。（遵循安全模式下的 open_basedir）
     * @return bool
     */
    public function setRule(string $rulePath)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->set_rule($rulePath);
        }
        return false;
    }

    /**
     * 设定分词返回结果时是否去除一些特殊的标点符号之类。
     *
     * @param bool $bool 设定值，如果为 true 则结果中不返回标点符号，如果为 false 则会返回，缺省为 false。
     * @return bool
     */
    public function setIgnore(bool $bool)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->set_ignore($bool);
        }
        return false;
    }

    /**
     * 设定分词返回结果时是否复式分割，如“中国人”返回“中国＋人＋中国人”三个词。
     *
     * @param int $mode 复合分词法的级别，缺省不复合分词。
     * @return bool
     */
    public function setMulti(int $mode)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->set_multi($mode);
        }
        return false;
    }

    /**
     * 设定是否将闲散文字自动以二字分词法聚合。
     *
     * @param bool $bool 设定值，如果为 true 则结果中多个单字会自动按二分法聚分，如果为 false 则不处理，缺省为 false。
     * @return bool
     */
    public function setDuality(bool $bool)
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->set_duality($bool);
        }
        return false;
    }

    /**
     * 根据$string的文本内容，返回一系列切好的词汇。每次切词后本函数应该循环调用，直到返回 false 为止，因为程序每次返回的词数是不确定的。
     *
     * @param string $string 需要切割的文本。
     * @return array | false
     */
    public function getResult(string $string = null)
    {
        if (!is_null(self::$scwsObject)) {
            if (is_string($string)) {
                $this->string = $string;
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->get_result();
            } elseif (is_null($string) && is_string($this->string)) {
                return self::$scwsObject->get_result();
            }
        }
        return false;
    }

    /**
     * 根据$string设定的文本内容，返回系统计算出来的最关键词汇列表。
     *
     * @param string $attr 可选参数，是一系列词性组成的字符串，各词性之间以半角的逗号隔开， 这表示返回的词性必须在列表中，如果以~开头，则表示取反，词性必须不在列表中，缺省为NULL，返回全部词性，不过滤。
     * @param string $string 需要计算的文本。
     * @param int $limit 可选参数，返回的词的最大数量，缺省是 10
     * @return array | false
     */
    public function getTops(string $attr = null, string $string = null, int $limit = 10)
    {
        if (!is_null(self::$scwsObject)) {
            if (is_string($string)) {
                $this->string = $string;
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->get_tops($limit, $attr);
            } elseif (is_null($string) && is_string($this->string)) {
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->get_tops($limit, $attr);
            }
        }
        return false;
    }

    /**
     * 根据$string设定的文本内容，返回系统中词性符合要求的关键词汇。
     *
     * @param string $attr 是一系列词性组成的字符串，各词性之间以半角的逗号隔开， 这表示返回的词性必须在列表中，如果以~开头，则表示取反，词性必须不在列表中，若为空则返回全部词。
     * @param string $string 需要查询的文本。
     * @return array | false
     */
    public function getWords(string $attr, string $string = null)
    {
        if (!is_null(self::$scwsObject)) {
            if (is_string($string)) {
                $this->string = $string;
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->get_words($attr);
            } elseif (is_null($string) && is_string($this->string)) {
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->get_words($attr);
            }
        }
        return false;
    }

    /**
     * 根据$string设定的文本内容，返回系统中是否包括符合词性要求的关键词。
     *
     * @param string $attr 是一系列词性组成的字符串，各词性之间以半角的逗号隔开， 这表示返回的词性必须在列表中，如果以~开头，则表示取反，词性必须不在列表中，若为空则返回全部词。
     * @param string $string 需要查询的文本。
     * @return bool
     */
    public function hasWords(string $attr, string $string = null)
    {
        if (!is_null(self::$scwsObject)) {
            if (is_string($string)) {
                $this->string = $string;
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->has_words($attr);
            } elseif (is_null($string) && is_string($this->string)) {
                self::$scwsObject->send_text($this->string);
                return self::$scwsObject->has_words($attr);
            }
        }
        return false;
    }

    /**
     * 返回 scws 版本号名称信息（字符串）。
     *
     * @return string | false
     */
    public function version()
    {
        if (!is_null(self::$scwsObject)) {
            return self::$scwsObject->version();
        }
        return false;
    }

    public function close()
    {
        if (!is_null(self::$scwsObject)) {
            self::$scwsObject->close();
        }
    }

    protected function __clone(){}

    public function __destrcut()
    {
        $this->close();
    }

}
