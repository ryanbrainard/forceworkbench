<?php
include_once "futures.php";

class ApexExecuteFutureTask extends FutureTask {

    private $executeAnonymousBlock;
    private $logCategory;
    private $logCategoryLevel;
    private $className;

    function __construct($executeAnonymousBlock, $logCategory, $logCategoryLevel) {
        parent::__construct();
        $this->executeAnonymousBlock = $executeAnonymousBlock;
        $this->logCategory = $logCategory;
        $this->logCategoryLevel = $logCategoryLevel;
       // $this->className = "ApexExecuteFutureTask";// TODO: $className; Need to dynamically get the class name with get_class()
    }

   /*  public function toJSON()
    {
        $arr = array(
            'executeAnonymousBlock' => $this->executeAnonymousBlock,
            'logCategory' => $this->logCategory,
            'logCategoryLevel' => $this->logCategoryLevel,
            'className' => $this->className,
        );
        return json_encode($arr);
    }

    public static function fromJSON($json)
    {
        $arr = json_decode($json, true);
        return new self(
            $arr['executeAnonymousBlock'],
            $arr['logCategory'],
            $arr['logCategoryLevel'],
            $arr['className']
        );
    } */

    function perform() {
        WorkbenchContext::get()->getApexConnection()->setDebugLevels($this->logCategory, $this->logCategoryLevel);
        $executeAnonymousResultWithDebugLog = WorkbenchContext::get()->getApexConnection()->executeAnonymous($this->executeAnonymousBlock);

        ob_start();
        if ($executeAnonymousResultWithDebugLog->executeAnonymousResult->success) {
            if (isset($executeAnonymousResultWithDebugLog->debugLog) && $executeAnonymousResultWithDebugLog->debugLog != "") {
                print("<pre>" . addLinksToIds(htmlspecialchars($executeAnonymousResultWithDebugLog->debugLog,ENT_QUOTES)) . '</pre>');
            } else {
                displayInfo("Execution was successful, but returned no results. Confirm log category and level.");
            }

        } else {
            $error = null;

            if (isset($executeAnonymousResultWithDebugLog->executeAnonymousResult->compileProblem)) {
                $error .=  "COMPILE ERROR: " . $executeAnonymousResultWithDebugLog->executeAnonymousResult->compileProblem;
            }

            if (isset($executeAnonymousResultWithDebugLog->executeAnonymousResult->exceptionMessage)) {
                $error .= "\nEXCEPTION: " . $executeAnonymousResultWithDebugLog->executeAnonymousResult->exceptionMessage;
            }

            if (isset($executeAnonymousResultWithDebugLog->executeAnonymousResult->exceptionStackTrace)) {
                $error .= "\nSTACKTRACE: " . $executeAnonymousResultWithDebugLog->executeAnonymousResult->exceptionStackTrace;
            }


            if (isset($executeAnonymousResultWithDebugLog->executeAnonymousResult->line)) {
                $error .=  "\nLINE: " . $executeAnonymousResultWithDebugLog->executeAnonymousResult->line;
            }

            if (isset($executeAnonymousResultWithDebugLog->executeAnonymousResult->column)) {
                $error .=  " COLUMN: " . $executeAnonymousResultWithDebugLog->executeAnonymousResult->column;
            }

            displayError($error);

            print ('<pre style="color: red;">' . addLinksToIds(htmlspecialchars($executeAnonymousResultWithDebugLog->debugLog,ENT_QUOTES)) . '</pre>');
        }
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}
