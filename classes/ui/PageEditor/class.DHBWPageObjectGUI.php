<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

use objects\DHBWTraining;

/**
 * Class DHBWPageObjectGUI
 *
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 *
 * @ilCtrl_isCalledBy DHBWPageObjectGUI: DHBWMainGUI
 * @ilCtrl_Calls      DHBWPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls      DHBWPageObjectGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class DHBWPageObjectGUI extends ilPageObjectGUI
{
    private DHBWTraining $training;

    public function __construct(DHBWTraining $training)
    {
        $this->training = $training;

        $this->checkAndAddCOPageDefinition();

        parent::__construct(ilDhbwTrainingPlugin::PLUGIN_ID, $this->training->getId());
    }

    protected function checkAndAddCOPageDefinition(): void
    {
        global $DIC;

        $sql_query = $DIC->database()->queryF('SELECT * FROM copg_pobj_def WHERE parent_type = %s', [ilDBConstants::T_TEXT], [ilDhbwTrainingPlugin::PLUGIN_ID]);

        if ($DIC->database()->numRows($sql_query) === 0) {
            $DIC->database()->insert('copg_pobj_def', [
                'parent_type' => [ilDBConstants::T_TEXT, ilDhbwTrainingPlugin::PLUGIN_ID],
                'class_name'  => [ilDBConstants::T_TEXT, DHBWPageObject::class],
                'directory'   => [ilDBConstants::T_TEXT, 'classes/Start/PageEditor'],
                'component'   => [ilDBConstants::T_TEXT, ilDhbwTrainingPlugin::getInstance()->getDirectory()]
            ]);
        }

        if (!DHBWPageObject::_exists(ilDhbwTrainingPlugin::PLUGIN_ID, $this->training->getId())) {
            $page_obj = new DHBWPageObject();
            $page_obj->setId($this->training->getId());
            $page_obj->setParentId($this->training->getId());
            $page_obj->create();
        }
    }

    public function executeCommand(): string
    {
        global $DIC;

        if (!ilObjDHBWTrainingAccess::hasWriteAccess()) {
            return "";
        }

        $html = parent::executeCommand();

        $this->fixTabs();

        $DIC->ui()->mainTemplate()->setContent($html);

        return $html;
    }

    protected function fixTabs(): void
    {
        global $DIC;

        $DIC->tabs()->removeTab('edit');
        $DIC->tabs()->removeTab('history');
        $DIC->tabs()->removeTab('clipboard');
        $DIC->tabs()->removeTab('pg');
    }


    public function getHTML() : string
    {
        global $DIC;

        $html = parent::getHTML();

        $this->fixTabs();

        $DIC->ui()->mainTemplate()->setContent($html);

        return $html;
    }
}