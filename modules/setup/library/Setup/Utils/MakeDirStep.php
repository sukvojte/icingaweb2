<?php
// {{{ICINGA_LICENSE_HEADER}}}
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Module\Setup\Utils;

use Icinga\Module\Setup\Step;

class MakeDirStep extends Step
{
    protected $paths;

    protected $dirmode;

    protected $errors;

    /**
     * @param array $paths
     * @param int   $dirmode    Directory mode in octal notation
     */
    public function __construct($paths, $dirmode)
    {
        $this->paths = $paths;
        $this->dirmode = octdec((string) $dirmode);
        $this->errors = array();
    }

    public function apply()
    {
        $success = true;
        foreach ($this->paths as $path) {
            if (false === file_exists($path)) {
                if (false === @mkdir($path)) {
                    $this->errors[$path] = error_get_last();
                    $success = false;
                } else {
                    $this->errors[$path] = null;
                    chmod($path, $this->dirmode);
                }
            }
        }

        return $success;
    }

    public function getSummary()
    {
        // This step is usually being used for directories which are required for the configuration but
        // are not defined in any way by the user. So there is no need to show a summary for this step.
    }

    public function getReport()
    {
        $okMessage = mt('setup', 'Directory "%s" in "%s" has been successfully created.');
        $failMessage = mt('setup', 'Unable to create directory "%s" in "%s". An error occured:');

        $report = '';
        foreach ($this->paths as $path) {
            if (array_key_exists($path, $this->errors)) {
                if (is_array($this->errors[$path])) {
                    $report .= '<p class="error">' . sprintf($failMessage, basename($path), dirname($path)) . '</p>'
                        . '<p>' . $this->errors[$path]['message'] . '</p>';
                } else {
                    $report .= '<p>' . sprintf($okMessage, basename($path), dirname($path)) . '</p>';
                }
            }
        }

        return $report;
    }
}
