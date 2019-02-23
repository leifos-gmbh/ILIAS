<?php
// we have to put this in a superior level to use a general table mapping
class submissionModel()
{
	/**
	 * GET all columns from the specific table
	 *
	 * create setters and getters for every single db column
	 *
	 * This data object will be used between the application class and the repository class
	 *
	 * e.g The ilExSubmission->uploadFile() will perform the logic and create an instance of this db object
	 * then will call ilSubmissionRepository->Add using this obj data as param.
	 *
	 * My question here:: repositories can not perform save/update methods. Is the controller/obj class responsible for such a thing?.
	 *
	 */
}
