<#1>
<?php
ilOrgUnitOperationContextQueries::registerNewContext(
        ilOrgUnitOperationContext::CONTEXT_USRF,
        ilOrgUnitOperationContext::CONTEXT_OBJECT
);

ilOrgUnitOperationQueries::registerNewOperation(
    ilOrgUnitOperation::OP_EDIT_USER_ACCOUNTS,
    'Edit User in User Administration',
    ilOrgUnitOperationContext::CONTEXT_USRF
);
?>