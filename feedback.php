<?php
require('../../config.php');
require_once($CFG->libdir . '/formslib.php');

use block_coursefeedback\form\feedback_form;

global $DB, $USER;

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($courseid);

if (has_capability('block/coursefeedback:viewdashboard', $context)) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('nopermissiontoviewfeedback', 'block_coursefeedback'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$PAGE->set_context($context);
$PAGE->set_url('/blocks/coursefeedback/feedback.php', ['courseid' => $courseid]);
$PAGE->set_title('Give Course Feedback');
$PAGE->set_heading('Give Course Feedback');

$existing = $DB->get_record('block_coursefeedback', ['userid' => $USER->id, 'courseid' => $courseid]);

$mform = new feedback_form(null, ['courseid' => $courseid]);

if ($existing) {
    $mform->set_data($existing);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($data = $mform->get_data()) {
    $record = (object)[
        'userid' => $USER->id,
        'courseid' => $data->courseid,
        'contentquality' => $data->contentquality,
        'instructoreffectiveness' => $data->instructoreffectiveness,
        'coursematerials' => $data->coursematerials,
        'workloaddifficulty' => $data->workloaddifficulty,
        'comments' => $data->comments,
        'timemodified' => time()
    ];

    if ($existing) {
        $record->id = $existing->id;
        $DB->update_record('block_coursefeedback', $record);
    } else {
        $DB->insert_record('block_coursefeedback', $record);
    }

    redirect(new moodle_url('/course/view.php', ['id' => $courseid]), '✅ Feedback submitted!');
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
