<?php
require('../../config.php');

global $DB, $USER, $PAGE, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);
$categoryfilter = optional_param('category', '', PARAM_TEXT);
$useridfilter = optional_param('userid', 0, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);

if (!has_capability('block/coursefeedback:viewdashboard', $context)) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]),
        get_string('nopermissiontoviewfeedback', 'block_coursefeedback'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$PAGE->set_context($context);
$PAGE->set_url('/blocks/coursefeedback/teacher_dashboard.php', ['courseid' => $courseid]);
$PAGE->set_title("Course Feedback Dashboard");
$PAGE->set_heading("Course Feedback Dashboard");

$where = "courseid = ?";
$params = [$courseid];

if (!empty($categoryfilter) && in_array($categoryfilter, ['contentquality', 'instructoreffectiveness', 'coursematerials', 'workloaddifficulty'])) {
    $where .= " AND $categoryfilter IS NOT NULL";
}

if (!empty($useridfilter)) {
    $where .= " AND userid = ?";
    $params[] = $useridfilter;
}

$feedbacks = $DB->get_records_select('block_coursefeedback', $where, $params);

$users = get_enrolled_users($context, 'block/coursefeedback:givefeedback');
$useroptions = ['0' => 'All Students'];
foreach ($users as $u) {
    $useroptions[$u->id] = fullname($u);
}

if (optional_param('export', '', PARAM_TEXT) === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=feedback.csv');
    echo "User Name,Content Quality,Instructor Effectiveness,Course Materials,Workload Difficulty,Comments\n";
    foreach ($feedbacks as $f) {
        $name = isset($users[$f->userid]) ? fullname($users[$f->userid]) : "User ID {$f->userid}";
        echo "\"{$name}\",{$f->contentquality},{$f->instructoreffectiveness},{$f->coursematerials},{$f->workloaddifficulty},\"{$f->comments}\"\n";
    }
    exit;
}

echo $OUTPUT->header();
echo html_writer::tag('h3', "Feedback for '{$course->fullname}'");

echo '<form method="get" style="margin-bottom: 1em;">';
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $courseid]);
echo ' &nbsp; Student: ';
echo html_writer::select($useroptions, 'userid', $useridfilter, false);
echo ' &nbsp;';
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Filter']);
echo '</form>';

$exporturl = new moodle_url('/blocks/coursefeedback/teacher_dashboard.php', [
    'courseid' => $courseid,
    'category' => $categoryfilter,
    'userid' => $useridfilter,
    'export' => 'csv'
]);
echo html_writer::link($exporturl, '📥 Export CSV');

echo '<br><br>';

if (!empty($feedbacks)) {
    $table = new html_table();
    $table->head = ['Student', 'Content Quality', 'Instructor Effectiveness', 'Course Materials', 'Workload Difficulty', 'Comments'];

    foreach ($feedbacks as $f) {
        $name = isset($users[$f->userid]) ? fullname($users[$f->userid]) : "User ID {$f->userid}";
        $table->data[] = [
            $name,
            $f->contentquality,
            $f->instructoreffectiveness,
            $f->coursematerials,
            $f->workloaddifficulty,
            format_text($f->comments)
        ];
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification("No feedback found.", 'info');
}

echo $OUTPUT->footer();
