const path = require('path');
const fs = require('fs');

// Plugins.
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

const findModules = (folderPath, modules) => {
	const jsFiles = [];

	for (const m of modules) {
		if (!m.includes('.php')) {
			if (fs.existsSync(path.resolve(__dirname, folderPath + '/' + m))) {
				if (fs.lstatSync(folderPath + '/' + m).isDirectory()) {
					const allFiles = fs.readdirSync(folderPath + '/' + m);
					for (const fileName of allFiles) {
						jsFiles.push(
							path.resolve(__dirname, folderPath + '/' + m + '/' + fileName)
						);
					}
				}
			}
		}
	}

	return jsFiles;
}

// Prepare object for populating.
const entries = {};

// Check script files.
for (const [name, path] of [['custom', './src/js/Core'], ['admin', './src/js/Admin']]) {
	if (fs.existsSync(path)) {
		entries[name] = [...findModules(path, fs.readdirSync(path))];
		if (entries[name].length < 1) {
			delete entries[name];
		}
	}
}

// Check styles.
const stylesLocation = './assets/styles/scss/';
if (fs.existsSync(stylesLocation + 'main.scss')) {
	entries.styles = [path.resolve(__dirname, stylesLocation + 'main.scss')];
}

module.exports = {
	entry: entries,
	resolve: {
	},
	output: {
		path: path.resolve(__dirname, './assets/build/'),
		filename: '[name].js',
	},
	optimization: {
		minimize: true,
		splitChunks: {
			minChunks: Infinity,
			chunks: 'all',
		},
	},
	module: {
		rules: [
			{
				test: /\.scss$/,
				exclude: '/node-modules/',
				use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
			},
		],
	},
	plugins: [
		new RemoveEmptyScriptsPlugin(),
		new MiniCssExtractPlugin({
			filename: 'custom.css',
		}),
	],
};
