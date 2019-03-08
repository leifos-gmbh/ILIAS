# COPage Editor

##Building

Buidling the js package `dist/Editor.js` requires installed npm.

`> npm run build`

Webpack is used to bundle the editor scripts into a package. See scripts tag in `package.json` and the `webpack.config.js` file.

##Architecture
###Client/Server Communication
Currently the editor is using a JSON RPC based API to perform ajax requests in the background. This is still open for discussion and may change. JSON RPC has some advantages and disadvantages compared to a RESTful API:
* Pro: Easy bulk operations in one request (also targeting multiple resources, e.g. deletion of a media object and update of a text paragraph).
* Pro: Independent from HTTP, e.g. might be send over websockets in the future.
* Pro: One end point (that is currently easy to integrate on top of the ilCtrl UI controller and routes through the existing permission check control flow).
* Con: Losing the HTTP semantics which enables HTTP speaking intermediaries to do e.g. caching or similar things.
