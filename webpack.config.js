const path = require('path')

module.exports = {
  entry: {
    aeria: './index.js',
  },
  context: path.resolve(__dirname, 'scripts'),
  output: {
    path: path.resolve(__dirname, 'assets/js'),
    filename: '[name].js',
  },
  module: {
    rules: [
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules\/(?!(@aeria)\/).*/,
        use: ['babel-loader']
      },
    ]
  }
}
