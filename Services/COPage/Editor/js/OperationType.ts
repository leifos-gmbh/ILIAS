/**
 * Operation types for the Operation class
 */
enum OperationType
{
    DeletePC = 'pc/delete',
    CreatePC = 'pc/create',
    ModifyPC = 'pc/modify',
    MovePC = 'pc/move',
    PageHtml = 'page/html',
    PageModel = 'page/model',
    UIButtons = 'ui/buttons',
    UIForms = 'ui/forms',
    UIDropdowns = 'ui/dropdowns'
}

export default OperationType;