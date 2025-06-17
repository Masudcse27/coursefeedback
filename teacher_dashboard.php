<?php
require('../../config.php');

global $DB, $USER, $PAGE, $OUTPUT;

// Params
$courseid = required_param('courseid', PARAM_INT);
$categoryfilter = optional_param('category', '', PARAM_TEXT);

// Load course and context
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
require_login($course);
$context = context_course::instance($courseid);

// Capability check
require_capability('block/coursefeedback:viewdashboard', $context);

// Set page settings
$PAGE->set_context($context);
$PAGE->set_url('/blocks/coursefeedback/teacher_dashboard.php', ['courseid' => $courseid]);
$PAGE->set_title("Course Feedback Dashboard");
$PAGE->set_heading("Course Feedback Dashboard");

// Filtering feedback
$where = "courseid = ?";
$params = [$courseid];

if (!empty($categoryfilter) && in_array($categoryfilter, ['contentquality', 'instructoreffectiveness', 'coursematerials', 'workloaddifficulty'])) {
    $where .= " AND $categoryfilter IS NOT NULL";
}

// Fetch feedback records
$feedbacks = $DB->get_records_select('block_coursefeedback', $where, $params);

// Export CSV
if (optional_param('export', '', PARAM_TEXT) === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=feedback.csv');
    echo "User ID,Content Quality,Instructor Effectiveness,Course Materials,Workload Difficulty,Comments\n";
    foreach ($feedbacks as $f) {
        echo "{$f->userid},{$f->contentquality},{$f->instructoreffectiveness},{$f->coursematerials},{$f->workloaddifficulty},\"{$f->comments}\"\n";
    }
    exit;
}

// Output page
echo $OUTPUT->header();

echo html_writer::tag('h3', "Feedback for '{$course->fullname}'");

// Filter form
// echo '<form method="get">';
// echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $courseid]);
// echo '<label for="category">Filter by category: </label>';
// echo '<select name="category" onchange="this.form.submit()">';
// echo '<option value="">-- All --</option>';
// foreach (['contentquality' => 'Content Quality', 'instructoreffectiveness' => 'Instructor Effectiveness', 'coursematerials' => 'Course Materials', 'workloaddifficulty' => 'Workload Difficulty'] as $key => $label) {
//     $selected = $key === $categoryfilter ? 'selected' : '';
//     echo "<option value=\"$key\" $selected>$label</option>";
// }
// echo '</select>';
// echo '</form>';

// Export button
$exporturl = new moodle_url('/blocks/coursefeedback/teacher_dashboard.php', [
    'courseid' => $courseid,
    'category' => $categoryfilter,
    'export' => 'csv'
]);
echo html_writer::link($exporturl, '📥 Export CSV');

// Table
if (!empty($feedbacks)) {
    $table = new html_table();
    $table->head = ['User ID', 'Content Quality', 'Instructor Effectiveness', 'Course Materials', 'Workload Difficulty', 'Comments'];

    foreach ($feedbacks as $f) {
        $table->data[] = [
            $f->userid,
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
