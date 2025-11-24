<?php
namespace enrol_nephilazip;

defined('MOODLE_INTERNAL') || die();

class course_renderer extends \core_course_renderer {

    protected function coursecat_coursebox(\coursecat_helper $chelper, $course, $additionalclasses = '') {
        $output = parent::coursecat_coursebox($chelper, $course, $additionalclasses);

        global $DB, $USER;
        $instance = $DB->get_record('enrol', ['enrol'=>'nephilazip','courseid'=>$course->id,'status'=>1]);

        if ($instance) {
            $price = number_format($instance->cost,2);
            $buyurl = new \moodle_url('/enrol/nephilazip/purchase.php', ['id'=>$course->id]);

            $output .= \html_writer::tag('div',
                get_string('price','enrol_nephilazip').": ₱$price ".
                \html_writer::link($buyurl,get_string('buy','enrol_nephilazip'),['class'=>'btn btn-primary']),
                ['class'=>'nephilazip-course-buy']
            );
        }

        return $output;
    }
}
