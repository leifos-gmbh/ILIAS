'use strict';
var path = require('path');

module.exports = {
	mode: 'development',
	entry: "./js/Editor.ts",
	output: {
		filename: "Editor.js",
		path: path.resolve(__dirname, 'dist')
	},
	resolve: {
		extensions: [".webpack.js", ".web.js", ".ts", ".js"]
	},
	module: {
		rules: [{ test: /\.ts$/, loader: "ts-loader" }]
	}
}