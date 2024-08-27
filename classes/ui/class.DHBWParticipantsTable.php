<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace ui;

use Exception;
use ilCheckboxInputGUI;
use ilDHBWTrainingPlugin;
use ilException;
use ilFormPropertyGUI;
use ilObjDHBWTrainingGUI;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTemplateException;
use ilTextInputGUI;
use legacy\LearningProgressStatusRepresentation;
use objects\Participant;
use objects\Training;
use platform\DHBWTrainingDatabase;
use platform\DHBWTrainingException;

/**
 * Class DHBWParticipantsTable
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWParticipantsTable extends ilTable2GUI
{
    private ilDHBWTrainingPlugin $plugin;
    private array $filter = [];
    private Training $training;

    /**
     * @throws ilException
     * @throws DHBWTrainingException
     * @throws Exception
     */
    public function __construct(?object $a_parent_obj, string $a_parent_cmd = "", Training $training)
    {
        global $DIC;

        $this->parent_obj = $a_parent_obj;

        $this->training = $training;

        $this->plugin = ilDHBWTrainingPlugin::getInstance();

        $this->setId("tbl_xdht_participants");
        $this->setPrefix("tbl_xdht_participants");
        $this->setFormName("tbl_xdht_participants");
        $DIC->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setRowTemplate("tpl.participants.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/DhbwTraining");

        $this->setFormAction($DIC->ctrl()->getFormActionByClass(ilObjDHBWTrainingGUI::class));
        $this->setExternalSorting(true);

        $this->setDefaultOrderField("full_name");
        $this->setDefaultOrderDirection("asc");
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);

        $this->initColumns();
        $this->addFilterItems();
        $this->parseData();
    }

    protected function initColumns()
    {

        $number_of_selected_columns = count($this->getSelectedColumns());
        $column_width = 100 / $number_of_selected_columns . '%';

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {

            $this->addColumn($all_cols[$col]['txt'], "$col", $column_width);
        }
    }

    public function getSelectableColumns(): array
    {
        $cols["full_name"] = array(
            "txt"     => $this->plugin->txt("participants_table_name"),
            "default" => true
        );
        $cols["login"] = array(
            "txt"     => $this->plugin->txt("participants_table_usr_name"),
            "default" => true
        );
        $cols["status"] = array(
            "txt"     => $this->plugin->txt("participants_table_learning_progress"),
            "default" => true
        );
        $cols["created"] = array(
            "txt"     => $this->plugin->txt("participants_table_first_access"),
            "default" => true
        );
        $cols["last_access"] = array(
            "txt"     => $this->plugin->txt("participants_table_last_access"),
            "default" => true
        );

        return $cols;
    }

    /**
     * @throws Exception
     */
    protected function addFilterItems()
    {
        $participant_name = new ilTextInputGUI($this->plugin->txt('participants_table_name'), 'full_name');
        $this->addAndReadFilterItem($participant_name);

        $usr_name = new ilTextInputGUI($this->plugin->txt('participants_table_usr_name'), 'login');
        $this->addAndReadFilterItem($usr_name);

        $status = new ilSelectInputGUI($this->plugin->txt("participants_table_learning_progress"), "status");

        $status->setOptions(LearningProgressStatusRepresentation::getDropdownDataLocalized($this->plugin));

        $this->addAndReadFilterItem($status);
    }

    /**
     * @throws Exception
     * @noinspection PhpParamsInspection
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function addAndReadFilterItem(ilFormPropertyGUI $item)
    {
        $this->addFilterItem($item);
        $item->readFromSession();
        if ($item instanceof ilCheckboxInputGUI) {
            $this->filter[$item->getPostVar()] = $item->getChecked();
        } else {
            $this->filter[$item->getPostVar()] = $item->getValue();
        }
    }

    /**
     * @throws DHBWTrainingException
     */
    protected function parseData()
    {
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $where = array(
            'training_obj_id' => $this->training->getId()
        );

        $database = new DHBWTrainingDatabase();

        $sorting_column = $this->getOrderField() ? $this->getOrderField() : 'full_name';
        $offset = $this->getOffset() ? $this->getOffset() : 0;

        $sorting_direction = $this->getOrderDirection();
        $num = $this->getLimit();

        foreach ($this->filter as $filter_key => $filter_value) {
            switch ($filter_key) {
                case 'full_name':
                case 'login':
                    $where[$filter_key] = '%' . $filter_value . '%';
                    break;
                case 'status':
                    if (!empty($filter_value)) {
                        $filter_value = LearningProgressStatusRepresentation::mappProgrStatusToLPStatus($filter_value);
                        $where[$filter_key] = $filter_value;
                        break;
                    }
            }
        }

        $collection = $database->select("rep_robj_xdht_partic", $where, null, "ORDER BY " . $sorting_column . " " . $sorting_direction . " LIMIT " . $offset . ", " . $num, ['LEFT', 'usr_data', 'usr_id', 'usr_id']);

        $this->setData($collection);
    }

    /**
     * @throws DHBWTrainingException
     * @throws ilTemplateException
     */
    public function fillRow($a_set): void
    {
        $participant = new Participant($a_set['id']);

        $usr_data = $this->getUserDataById($participant->getUsrId());

        if ($this->isColumnSelected('full_name')) {
            $this->tpl->setCurrentBlock("PARTICIPANT_NAME");
            $this->tpl->setVariable('PARTICIPANT_NAME', $usr_data['firstname'] . " " . $usr_data['lastname']);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('login')) {
            $this->tpl->setCurrentBlock("USR_NAME");
            $this->tpl->setVariable('USR_NAME', $usr_data['login']);
            $this->tpl->parseCurrentBlock();
        }
        if ($this->isColumnSelected('status')) {
            $this->tpl->setCurrentBlock("LEARNING_PROGRESS");
            $this->tpl->setVariable('LP_STATUS_ALT', LearningProgressStatusRepresentation::statusToRepr($participant->getStatus()));
            $this->tpl->setVariable('LP_STATUS_PATH', LearningProgressStatusRepresentation::getStatusImage($participant->getStatus()));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('created')) {
            $this->tpl->setCurrentBlock("CREATED");
            $this->tpl->setVariable('CREATED', $participant->getCreated()->format('d.m.Y H:i:s'));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isColumnSelected('last_access')) {
            $this->tpl->setCurrentBlock("LAST_ACCESS");
            $this->tpl->setVariable('LAST_ACCESS', $participant->getLastAccess()->format('d.m.Y H:i:s'));
            $this->tpl->parseCurrentBlock();
        }
    }


    protected function getUserDataById($usr_id): ?array
    {
        global $ilDB;
        $q = "SELECT * FROM usr_data WHERE usr_id = " . $ilDB->quote($usr_id, "integer");
        $usr_set = $ilDB->query($q);
        return $ilDB->fetchAssoc($usr_set);
    }
}