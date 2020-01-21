module.exports = {
  sourceMaps: true,
  'presets': [
    ['@babel/env', {
      'useBuiltIns': 'usage',
      'corejs': 3
    }],
    ['@babel/preset-react']
  ],
  'plugins': [
    'babel-plugin-styled-components',
    [
      '@babel/plugin-proposal-decorators', {
        legacy: true
      }
    ],
    '@babel/plugin-proposal-class-properties',
    '@babel/plugin-syntax-dynamic-import'
  ]
}
