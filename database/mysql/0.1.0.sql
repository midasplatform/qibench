CREATE TABLE IF NOT EXISTS `qibench_run` (
  `qibench_run_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `executable_name` text NOT NULL,
  `params` text NOT NULL,
  `batchmake_task_id` bigint(20) NOT NULL,
  `input_folder_id` bigint(20) NOT NULL,
  `output_folder_id` bigint(20) NOT NULL,
  `condor_dag_id` bigint(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`qibench_run_id`)
)   DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qibench_run_item` (
  `qibench_run_item_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `qibench_run_id` bigint(20) NOT NULL,
  `case_id` text NOT NULL,
  `lesion_id` text NOT NULL,
  `input_item_id` bigint(20) NOT NULL,
  `output_item_id` bigint(20) NOT NULL,
  `condor_dag_job_id` bigint(20) NOT NULL,
  PRIMARY KEY (`qibench_run_item_id`)
)   DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `qibench_run_item_scalarvalue` (
  `qibench_run_item_scalarvalue_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `qibench_run_item_id` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`qibench_run_item_scalarvalue_id`)
)   DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `qibench_lesionseedpoint` (
  `qibench_lesionseedpoint_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `case_id` bigint(10) NOT NULL,
  `lesion_id` bigint(10) NOT NULL,
  `seed_x` float NOT NULL,
  `seed_y` float NOT NULL,
  `seed_z` float NOT NULL,
  `bounding_box_x0` float NOT NULL,
  `bounding_box_x1` float NOT NULL,
  `bounding_box_y0` float NOT NULL,
  `bounding_box_y1` float NOT NULL,
  `bounding_box_z0` float NOT NULL,
  `bounding_box_z1` float NOT NULL,
  `is_in_physical_space` boolean NOT NULL,
  PRIMARY KEY (`qibench_lesionseedpoint_id`),
  UNIQUE unique_case_lesion (`case_id`, `lesion_id`)
)   DEFAULT CHARSET=utf8;
