This README is work in progress. It currently focuses on commands that change state, not on querying. Some additional concepts may be added if necessary, e.g. middlewares or former command bus abstraction.

# ILIAS API

The ILIAS API layer puts the focus on domain level actions and queries. It enables access to high level business logic of ILIAS components and separates it from front end logic like the User Interface, SOAP or the Workflow Engine.

The main goals of the API development are:

- Easy to use in consuming code like other components, front-ends or plugins.
- Clear structure for API implementation 
- Full documentation
- Fostering policy compliance
- Re-use of sub-APIs in higher contexts (e.g. membership API in course or group contexts)

## Scope of the API

The API includes methods to perform typical user actions (called commands) of the ILIAS plattform. If something can be performed in ILIAS by hitting a button, sending a form or watching a view in the ILIAS user interface, it should probably be available through a command in the API, too - without a user interface.

The API does not include low level infrastructure actions that are only parts of larger user processes.

Examples

- The command to add a member to a course should be part of the API. Executing the command should include all the necessary checks (e.g. has the actor the permission to add the member) and supporting actions that are also performed if the action is done through the user interface, e.g. sending notifications, writing log entries and so on.
- The action to write a log entry MUST not be available through the API. This is considered as a supporting action that is only part of a larger user commands.

Performing commands should leave the system always in a consistent state.

This implies an important rule: **Commands MUST not be nested**. The implementation of commands (located in so-called command handlers) MUST not call other commands through the API. This contradicts the rule that only top level commands are accessible through the API. This rule also exists to keep the overall processing simple and to ensure performance (e.g. policy checks should be done only one time).

## Using the API

Using the API consists of two main steps

1. Retrieving a command object.
2. Executing the command under an actor.

```
$actor_id = $DIC->user()->getId();
$api = $DIC->api();

// get a command for adding a member with user id 100
// to a local role with id 200 to a course with ref_id 7
$add_member_cmd = $api->course(7)->membership()->add(100, 200);

try {
	// execute the command under a user with id $actor_id
	$api->dispatch($add_member_cmd, $actor_id);
}
catch (Exception $e)
{
	// handle the exception
	...
}
```

## Basic Structure

The API is available through the DIC via `$api = $DIC->api();`.

All top level domain components will provide factories for their commands or sub-APIs directly under this object.

```
$course_api = $DIC->api()->course(...);
$group_api = $DIC->api()->group(...);
$test_api = $DIC->api()->test(...);
$exercise_api = $DIC->api()->exercise(...);
...
```

Cross-functional sub-APIs will be available in the context of the top level APIs.

```
$course_membership_api = $DIC->api()->course(...)->membership(...);
$course_metadata_api = $DIC->api()->course(...)->metadata(...);
...
```

## Concepts

### Command Bus

The API mainly follows a command bus pattern. The domain logic is available through single commands that are passed to a command dispatcher (bus) which enforces a common execution process for commands.

### Commands and Command Factories

A command is represented by a simple data object retrieved from the commmand factory chain of the API.

```
$add_member_cmd = $api->course(7)->membership()->add(100, 200);
```

The `course()` and `membership()` calls will return command factories and the last call `add()` will return a command object.

### Command Dispatching

To execute a command it will be passed to the command bus by calling `$api->dispatch(...)`. This method takes two arguments, the command object and the actor id.

### Parameters

All values passed to factories and commands (in the example above 7, 100, 200) are called Parameters.

### Command Handlers

The execution process will trigger command handlers of the components that are involved during the creation of a command object. This means that for the following example two handlers will be involved, the **course** and the **membership** command handler.
```
$add_member_cmd = $api->course(7)->membership()->add(100, 200);
```

Command handlers are dealing with
- Policy Enforcement
- Configuration of sub-handlers
- Execution of commands

### Policy Enforcement

The command bus process will enforce policy checks (e.g. permission checks) to confirm that the provided actor is entitled to perform the command and that all parameters are valid. Checks will be done by all components that are involved in the factory chain when creating the command object (in the current example **course** and **membership**).

### Configuration of sub-handlers

Upper level command handlers may or sometimes must configure sub-handlers. E.g. the membership API may provide a way to configure its command handler for upper components like courses or groups.

### Execution of commands

The final command execution has to be implemented by the handler of the last involved component (in the current example the **membership** component). The execution is performed after all upper level components succeded with their policy checks and after they (sometimes optionally) configured their subsequent sub-handlers.

## Implementing the API

### Adding a top level domain component
The top level domains need to add their main command factory to `src/API/API.php` and the corresponding interface.

All other code goes to your component and should use a namespace `ILIAS\API\ComponentName`, e.g. `namespace ILIAS\API\Course;`.

### Implementing a Command Factory

The command factory of your component **MUST**

- be a class named `CommandFactory`
- extend `\ILIAS\API\Int\AbstractCommandFactory`
- implement `\ILIAS\API\Int\CommandFactory`
- retrieve a `\ILIAS\API\Int\FactoryCollection` object via constructor and pass it to its parent constructor
- implement methods that return `\ILIAS\API\Int\Command` objects (commands) and/or `\ILIAS\API\Int\CommandFactory` objects (sup-API command factories)

If a command factory accepts **parameters** it MUST pass a `Parameter` object to the parent constructor (see next chapter).

Example
```
namespace ILIAS\API\Course;
use ILIAS\API\Course\Int as I;
use ILIAS\API as API;

/**
 * Course api command factory
 * ...
 */
class CommandFactory extends API\Int\AbstractCommandFactory implements I\CommandFactory
{
	/**
	 * Constructor
	 */
	public function __construct(API\Int\FactoryCollection $factory_collection, int $course_ref_id = null)
	{
		// accepting parameters and passing them to parent via parameter object
		$pars = new Parameters($course_ref_id);
		parent::__construct($factory_collection, $pars);
	}

	/**
	 * Returning a Sup-API
	 */
	function membership(): \ILIAS\API\Membership\Int\CommandFactory {
		return new \ILIAS\API\Membership\CommandFactory($this->factory_collection);
	}

	/**
	 * Returning a command...
	 */
	function create(string $title, string $description, int $parent_ref_id): I\CreateCommand {
		return new CreateCommand($this->factory_collection, $title, $description, $parent_ref_id);
	}
}
```

### Implementing Parameter Objects

Parameter objects are used to retrieve consumer parameters during command object instatiation and pass them later to the corresponding command handlers.

Parameter objects **MUST**

- implement `\ILIAS\API\Int\Parameters`
- be implemented as an immutable value object that retrieves all its parameters through the constructor and corresponding get...() methods for accessing the parameters.

Example
```
namespace ILIAS\API\Course;
use ILIAS\API as API;

/**
 * Course api factory parameters
 * ...
 */
class Parameters implements API\Int\Parameters
{
	protected $course_ref_id = null;

	/**
	 * Constructor
	 */
	public function __construct(int $course_ref_id = null)
	{
		$this->course_ref_id = $course_ref_id;
	}

	/**
	 * @return int|null
	 */
	function getCourseRefId(): ?int
	{
		return $this->course_ref_id;
	}
}
```

### Implementing a Command

A command of your component **MUST**

- be a class suffixed with `Command` e.g. `AddCommand`
- extend `\ILIAS\API\Int\AbstractCommand`
- implement `\ILIAS\API\Int\Command`
- retrieve a `\ILIAS\API\Int\FactoryCollection` object via constructor and pass it to its parent constructor
- be implemented as an immutable value object that retrieves all its parameters through the constructor and corresponding get...() methods for accessing the parameters.

Example
```
namespace ILIAS\API\Membership;
use ILIAS\API\Membership\Int as I;
use ILIAS\API as API;

/**
 * Add membership command
 */
class AddCommand extends API\Int\AbstractCommand implements I\AddCommand {

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var int
	 */
	protected $local_role_id;

	/**
	 * CreateCommand constructor.
	 * @param int $user_id
	 * @param int $local_role_id
	 */
	public function __construct(API\Int\FactoryCollection $factory_collection, int $user_id, int $local_role_id) {
		parent::__construct($factory_collection);
		$this->user_id = $user_id;
		$this->local_role_id = $local_role_id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}

	/**
	 * @return int
	 */
	public function getLocalRoleId(): int {
		return $this->local_role_id;
	}
}

```

### Integrating Sub-APIs

Sub-APIs are integrated into Command Factories my implementing a method that returns the command factory of the sub component.

The method MUST
- be named according to the sub component (e.g. if the subcomponent is Membership the method must be named membership()).
- return the specific extended `\ILIAS\API\Int\CommandFactory` interface e.g. 
```
public function membership(): \ILIAS\API\Membership\Int\CommandFactory;
```

### Configuring Sub-APIs

tbd

### Implementing Command Handlers

Command handlers **MUST**

- be a class named `CommandHandler`
- extend `\ILIAS\API\Int\AbstractCommandHandler`
- implement a method `checkPolicyForSubHandler` that checks if a sub commmand handler
  may be called by the current actor and if yes, return self::POLICY_OK otherwise self::POLICY_FAILED. E.g. a course command handler may check the `manageMembers` permission before allowing to pass the command to a Membership command handler.
- implement a method `checkPolicyForCommand` that checks if a commmand
  may be performed by the current actor and if yes, return self::POLICY_OK otherwise self::POLICY_FAILED.
- implement a method `handle` that performs the command. Note that **this method MUST not call the check-methods again**, since this is already done by the command bus.

Command handlers **MUST NOT**

- Make use of the API to call other commands.

Example
```
namespace ILIAS\API\Course;

use ILIAS\API as API;
use \ILIAS\API\Exceptions as Exc;


/**
 * Command handler
 * ...
 */
class CommandHandler extends API\Int\AbstractCommandHandler
{
	/**
	 * @inheritdoc
	 */
	public function getConfigForSubHandler(API\Int\CommandHandler $sub_handler)
	{
		// @todo check sub handlers and configure them

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function checkPolicyForSubHandler(API\Int\CommandHandler $sub_handler, int $actor_id): int
	{
		// membership subhandler
		if ($sub_handler instanceof \ILIAS\API\Membership\CommandHandler)
		{
			// check manage members permission

			/** @var Parameters $p */
			$p = $this->getParameters();

			// pseudo check: if ref id is 7 everything is ok
			if ($p->getCourseRefId() == 7)
			{
				return self::POLICY_OK;
			};
		}

		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function checkPolicyForCommand(API\Int\Command $command, int $actor_id): int
	{
		// create command
		if ($command instanceof CreateCommand)
		{
			/** @var CreateCommand $command */
			$parent_ref_id = $command->getParentRefId();

			// pseudo check: if ref id is 7 everything is ok
			if ($parent_ref_id == 7)
			{
				return self::POLICY_OK;
			};
		}
		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(API\Int\Command $command, int $actor_id)
	{
		// create command
		if ($command instanceof CreateCommand)
		{
			/** @var CreateCommand $command */
			$title = $command->getTitel();
			$description = $command->getDescription();
			$parent_ref_id = $command->getParentRefId();

			// now code for performing the command
			// ....

			return;
		}
		throw new Exc\UnknownCommand("Command unknown $command");
	}

}
```