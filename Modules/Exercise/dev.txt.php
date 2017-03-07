<?php exit; ?>

#### 7/3/2017 INSTRUCTION FILES - DISPLAYED IN VIEW MODE

## MIGRATION
#### Instruction Files migration from outside ILIAS directory to ILIAS "data" directory

File -> **patch_exc_move_instruction_files.php**

I assume that all the files located in "ass_XXX" --> outside ilias /outside_data_directory/client_name/ilExercise/X/exc_XXX/ass_XXX/0
are instruction files.

We had doubts about where are the solution files located and this files are located in directories like this: feedb_xx/0/xxxx.xx

So The patch moves all the content in ass_ directories.


## FEATURE IMPLEMENTATION
Save and show instruction files located inside root directory instead of outside data directory.

	- (edit) **include/inc.ilias_version.php** change ILIAS version

			define("ILIAS_VERSION", "5.3.0 2017-02-07");
			define("ILIAS_VERSION_NUMERIC", "5.3.0");

	- (new class) **Modules/Exercise/classes/class.ilFSWebStorageExercise.php** extending ilFileSystemStorage class.
	Stores the files inside ILIAS data directory.
	important to know, in the construct:

		parent::__construct(self::STORAGE_WEB,true,$a_container_id);


	- (edit) **Modules/Exercise/classes/class.ilExerciseExporter.php**

		- (edit)**getValidSchemaVersions()** method: Add new ILIAS version

				"5.2.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_2",
				"xsd_file" => "ilias_exc_5_2.xsd",
				"uses_dataset" => true,
				"min" => "5.2.0",
				"max" => "5.2.99"),
				"5.3.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_3",
				"xsd_file" => "ilias_exc_5_3.xsd",
				"uses_dataset" => true,
				"min" => "5.3.0",
				"max" => "")

	- (edit) **Modules/Exercise/classes/class.ilExerciseDataSet.php**

		- (edit) **getSupportedVersions()** method: Add new ILIAS version

				return array("4.1.0", "4.4.0", "5.0.0", "5.1.0", "5.2.0", "5.3.0");

		- (edit) **getTypes()** method: Add new ILIAS version, with same code as 5.2

				if ($a_entity == "exc")
				{
					switch ($a_version)
					{
						...
						case "5.2.0":
						case "5.3.0":
						...
						...
						...


				if ($a_entity == "exc_assignment")
				{
					switch ($a_version)
					{
						case "5.3.0": //same as 5.2.0 + add WebDataDir
							return array(
								...

								"WebDataDir" => "directory"

								...
							);
					...

		- (edit) **readData()** method: Add the ILIAS version. Same code as 5.2

				...
				case "5.2.0":
				case "5.3.0":
				...

		- (edit) **getXmlRecord** method: store the setWebDataDir path.

				//now the instruction files inside the root directory
				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fswebstorage = new ilFSWebStorageExercise($a_set['ExerciseId'], $a_set['Id']);
				$a_set['WebDataDir'] = $fswebstorage->getPath();

		- (edit) **importRecord()** method: instruction files into web data dir., all the others are stored as always.
			we were talking about if $a_rec["WebDataDir"] use one class, else the other one. But both are needed.
			- ilFSWebStorageExercise for instruction files.
			- ilFSStorageExercise for all the other files.

				// (5.3) assignment files inside ILIAS
				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fwebstorage = new ilFSWebStorageExercise($exc_id, $ass->getId());
				$fwebstorage->create();
				$dir = str_replace("..", "", $a_rec["WebDataDir"]);
				if ($dir != "" && $this->getImportDirectory() != "")
				{
					$source_dir = $this->getImportDirectory()."/".$dir;
					$target_dir = $fwebstorage->getPath();
					ilUtil::rCopy($source_dir, $target_dir);
				}

	- (edit) **Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php**

		- (edit) **executeCommand** method: case ilfilesystemgui: use ilFSWebStorageExercise instead of ilFSStorageExercise

				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fWebStorage = new ilFSWebStorageExercise($this->exercise_id, $this->assignment->getId());
				$fWebStorage->create();

	- (edit) **Modules/Exercise/classes/class.ilExAssignment.php**

		- (edit) **getFiles()** method: ilFSWebStorageExercise instead of ilFSStorageExercise

		- (edit) **uploadAssignmentFiles()** method: ilFSWebStorageExercise instead of ilFSStorageExercise


	- (edit) **Modules/Exercise/classes/class.ilExAssignmentGUI.php**

		- (edit) **addFiles()** method: Represent the files depending of its type

		- (edit) **addSubmissionFeedback()** method: include the new class.

				include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");


---

## MANTIS BUG :0019795
It is not possible to remove files from a peer feedback from a exercise.

The problem seems the file path creation and affects both feedback with and without criteria.

Example:
User ID who did the exercise: 310
User ID who provide feedback: 6
Feedback file: feedback.txt
Criteria ID = 10

Without criteria the uploaded files are stored outside the final path. The name of the file is also affected.

data/client/ilExercise/3/exc_343/peer_up_15/310/6/ [empty directory]
data/client/ilExercise/3/exc_343/peer_up_15/310/6feedback.txt

After patch:

data/client/ilExercise/3/exc_343/peer_up_15/310/6/feedback.txt


With criteria, the final directory name is userid+criteriaid instead of criteria id.

data/client/ilExercise/3/exc_343/peer_up_15/310/610/feedback.txt

After patch:

data/client/ilExercise/3/exc_343/peer_up_15/310/6/10/feedback.txt

## We need to take a look at how to proceed with the migration of the old directories/files.
