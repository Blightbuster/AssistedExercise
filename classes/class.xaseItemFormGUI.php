<?php

/**
 * Class xaseItemFormGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Services/UIComponent/Button/classes/class.ilJsLinkButton.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSampleSolution.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilHintInputGUI.php');

class xaseItemFormGUI extends ilPropertyFormGUI
{
    const M1 = 1;
    const M2 = 2;
    const M3 = 3;

    const CMD_ADD_HINT = 'addHint';
    const CMD_REMOVE_HINT = 'removeHint';

    /**
     * @var  xaseItem
     */
    protected $object;
    /**
     * @var xaseItemGUI
     */
    protected $parent_gui;

    /*
    * @var  ilCtrl
    */
    protected $ctrl;

    /**
     * @var ilAssistedExercisePlugin
     */
    protected $pl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var xaseSettings
     */
    protected $xase_settings;

    /**
     * @var xaseSettingsM1|null|xaseSettingsM3
     */
    protected $mode_settings;

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * @var boolean
     */
    protected $is_creation_mode;

    /**
     * @var int
     */
    protected $mode;

    public function __construct($parent_gui, xaseItem $xaseItem, $mode)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->object = $xaseItem;
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->tpl = $this->dic['tpl'];
        $this->ctrl = $this->dic->ctrl();
        $this->parent_gui = $parent_gui;
        $this->mode = $mode;
        $this->xase_settings = xaseSettings::where(['assisted_exercise_object_id' => $this->object->getId()])->first();
        if($this->xase_settings->getModus() === self::M1) {
            $this->mode_settings = xaseSettingsM1::where(['settings_id' => $this->xase_settings->getId()])->first();
        } elseif($this->xase_settings->getModus() === self::M3) {
            $this->mode_settings = xaseSettingsM3::where(['settings_id' => $this->xase_settings->getId()])->first();
        }
        parent::__construct();

        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/hint.js');
        $this->initForm();
    }

    public function initForm()
    {
        $this->setTarget('_top');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->pl->txt('item_create'));

        $ti = new ilTextInputGUI($this->pl->txt('title'), 'title');
        $ti->setRequired(true);
        $this->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->pl->txt('task'), 'task');
        $ta->setRequired(true);
        $ta->setRows(10);
        $ta->setInfo($this->pl->txt('info_hints'));
        $this->addItem($ta);

        if ($this->mode == 1 || $this->mode == 3) {
            $this->initM1AndM3Form();
        }

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle( $this->pl->txt( $this->pl->txt('hints')));
        $this->addItem( $header );

        $this->initHintForm();

        $this->addCommandButton(xaseItemGUI::CMD_UPDATE, $this->pl->txt('save'));
        $this->addCommandButton(xaseItemGUI::CMD_STANDARD, $this->pl->txt("cancel"));

        $this->setFormAction($this->ctrl->getFormAction($this));
    }

    public function initM1andM3Form()
    {
        $this->initAddHintBtn();

        //TODO Decide based on Modus wether this input is required or not
        $sol = new ilTextAreaInputGUI($this->pl->txt('sample_solution'), 'sample_solution');

        if($this->xase_settings->getModus() === '1') {
            if($this->mode_settings->getSampleSolutionVisible()) {
                $sol->setRequired(true);
            } else {
                $sol->setRequired(false);
            }
        }

        $sol->setRows(10);
        $this->addItem($sol);

        $max_points = new ilNumberInputGUI($this->pl->txt('specify_max_points'), 'max_points');
        $max_points->setRequired(true);
        $max_points->setSize(4);
        $max_points->setMaxLength(4);
        $this->addItem($max_points);

    }

    public function initAddHintBtn() {
        $tpl = new ilTemplate('tpl.add_hint_button_code.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
        $btn_add_hint = ilJsLinkButton::getInstance();
        $btn_add_hint->setCaption('text_hint_btn');
        $btn_add_hint->setName('hint_btn');
        $btn_add_hint->setId('hint_trigger_text');
        $tpl->setCurrentBlock('CODE');
        $tpl->setVariable('BUTTON', $btn_add_hint->render());
        $tpl->parseCurrentBlock();
        $custom_input_gui = new ilCustomInputGUI();
        $custom_input_gui->setHtml($tpl->get());
        $this->addItem($custom_input_gui);
    }

    public function initRemoveHintBtn() {
        $tpl = new ilTemplate('tpl.remove_hint_button_code.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
        $btn_remove_hint = ilJsLinkButton::getInstance();
        $btn_remove_hint->setCaption('text_remove_hint_btn');
        $btn_remove_hint->setName('text_remove_hint_btn');

        $tpl->setCurrentBlock('CODE');
        $tpl->setVariable('BUTTON', $btn_remove_hint->render());
        $tpl->parseCurrentBlock();
        $custom_input_gui = new ilCustomInputGUI();
        $custom_input_gui->setHtml($tpl->get());
        $this->addItem($custom_input_gui);
    }

    public function initHint2Form() {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->pl->txt('Hint 1'));
        $this->addItem($header);

        $hint_input_gui = new ilHintInputGUI();
        $hint_input_gui->setRemoveHintBtn($this->initRemoveHintBtn());
        $this->addItem($hint_input_gui);
    }

    public function initHintForm() {
        $hint_input_gui = new ilHintInputGUI($this->pl->txt('hints'), "");
        $this->addItem($hint_input_gui);
        return $this;
    }

    public function applyIndicesToTaskText( $task )
    {
        $parts	= explode( '[hint', $task );
        $i = 0;
        $task = '';
        foreach ( $parts as $part )
        {
            if ( $i == 0 )
            {
                $task .= $part;
            }
            else
            {
                $task .= '[hint ' . $i . $part;
            }
            $i++;
        }
        return $task;
    }

    public function fillForm()
    {
        $array = array (
            'title' => $this->object->getTitle(),
            'task' => $this->object->getTask()

        );
        if ($this->mode == 1 || $this->mode == 3) {
            /**
             * @var xaseSampleSolution $xaseSampleSolution
             */
            $xaseSampleSolution = xaseSampleSolution::where(array('id' => $this->object->getSampleSolutionId()))->get();
            $array["sample_solution"] = $xaseSampleSolution->getSolution();
            $xasePoints = xasePoint::where(array('id' => $this->object->getPointId()))->get();
            /**
             * @var xasePoint $xasePoints
             */
            $array["specify_max_points"] = $xasePoints->getMaxPoints();

            //TODO finish create create fillForm
            $hints = $this->getHintsByItem($this->object->getId());



        }
    }

    protected function getHintsByItem($item_id) {
        return xaseHint::where(array('item_id' => $item_id))->get();
    }

    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }


        return true;
    }

    /**
     * @return bool|string
     */
    public function updateObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
        $this->object->store();
        return true;
    }

}