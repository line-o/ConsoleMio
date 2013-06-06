<?php
/**
 * @author Juri Leino <github@line-o.de>
 * Date: 03.06.13
 * Time: 21:37
 */

class ConsoleMio {
    /**
     * @var resource
     */
    var $resource = STDOUT;

    /**
     * @var int
     */
    var $options = 0;
    var $indent = 0;

    static protected $levels = array(
        'group' => 'grey',
        'log'   => 'white',
        'debug' => 'green',
        'info'  => 'blue',
        'warn'  => 'yellow',
        'error' => 'red'
    );
    static protected $colors = array(
        'reset' => '0',
        'gray'   => '0;11',
        'white'  => '0;37',
        'green'  => '0;32',
        'blue'   => '0;34',
        'yellow' => '0;33',
        'red'    => '0;31'
    );
    static $emulatePrettyPrint = false;

    /**
     * @var callable
     */
    protected $encoder;

    function __construct($resource = null) {
        // TODO set color codes according to system specs
        $this->resource = (is_resource($resource)) ? $resource : STDOUT;
        $this->setEncoderOptions(self::getDefaultOptions());
    }

    /**
     * @param int $options combined flags passed to json_encode
     * @return void
     */
    function setEncoderOptions($options) {
        if (!is_int($options) || $options < 0) {
            return;
        }
        $this->options = $options;
        $emulatePrettyPrint = self::$emulatePrettyPrint;
        $this->encoder = function ($argument) use ($options, $emulatePrettyPrint) {
            $json = json_encode($argument, $options);
            if ($emulatePrettyPrint) {

            }
            return $json;
        };
    }

    /**
     * @param string $level
     * @param string $label
     * @param array $args
     */
    protected function _log($level = 'log', $label = '',  $args = array()) {
        $this->_out($this->_format($level, $label,  $this->argsToString($args)));
    }

    /**
     * @param  array $args
     * @return string
     */
    protected function argsToString ($args) {
        return implode(' ', array_map($this->encoder, $args));
    }

    protected function _format($level, $label,  $data) {
        static $template = "\n{indent}{color}{label}  {data}{reset}";
        static $search   = array('{color}', '{label}', '{indent}', '{data}', '{reset}');
        $color = $this->_color(self::$levels[$level]);
        $reset = $this->_color('reset');
        $indent = str_repeat("\xE2\x94\x83  ", $this->indent);
        return str_replace($search, array( $color, $label, $indent, $data, $reset), $template);
    }

    protected function _color($name) {
        if (!array_key_exists($name, self::$colors)) {
            $name = 'gray';
        }
        $code = self::$colors[$name];
        static $cmd = "\033[%sm"; // %{$fg[red]%}%
        return sprintf($cmd, $code);
    }

    /**
     * wraps call to fwrite
     * @param string $msg
     */
    protected function _out ($msg) {
        fwrite($this->resource, $msg);
    }

    function group($name) {
        $label = "\xE2\x95\xAD {$name} ";
        $this->_log('group', $label);
        $this->indent++;
    }

    function endGroup() {
        $this->indent = ($this->indent-- > 0) ? $this->indent : 0;
        $label = "\xE2\x95\xB0";
        $this->_log('group', $label);
    }

    /**
     */
    function log() {
        $this->_log('log', " ", func_get_args());
    }

    /**
     */
    function debug() {
        $this->_log('debug', "\xF0\x9F\x94\x8E", func_get_args());
    }

    /**
     */
    function info() {
        $this->_log('info', "\xE2\x93\x98", func_get_args());
    }

    /**
     */
    function warn() {
        $this->_log('warn', "\xE2\x9A\xA0", func_get_args());
    }

    /**
     */
    function error() {
        $this->_log('error', "\xF0\x9F\x92\xA3", func_get_args());
    }

    /**
     * @return int
     */
    static function getDefaultOptions() {
        $flags = JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT;
        if (PHP_VERSION > 5.4) {
            return $flags | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_BIGINT_AS_STRING;
        }
        self::$emulatePrettyPrint = true;
        return $flags;
    }
}
