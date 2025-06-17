<?php
namespace block_coursefeedback\form;

require_once("$CFG->libdir/formslib.php");

class feedback_form extends \moodleform {
    public function definition() {
        $ratings = [
            1 => '1 - Poor',
            2 => '2 - Fair',
            3 => '3 - Good',
            4 => '4 - Very Good',
            5 => '5 - Excellent'
        ];

        $mform = $this->_form;
        $mform->setDefault('courseid', $this->_customdata['courseid']);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $categories = [
            'contentquality' => 'Content Quality',
            'instructoreffectiveness' => 'Instructor Effectiveness',
            'coursematerials' => 'Course Materials',
            'workloaddifficulty' => 'Workload Difficulty'
        ];

        foreach ($categories as $key => $label) {
            $mform->addElement('select', $key, $label, $ratings);
            $mform->setType($key, PARAM_INT);    
            $mform->setDefault($key, 3); 
        }

        $mform->addElement('textarea', 'comments', 'Comments', 'wrap="virtual" rows="4" cols="50"');
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
