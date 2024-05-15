const config = require( '@wordpress/scripts/config/.prettierrc.js' );

const newConfig = {
	...config,
	printWidth: 120,
};
// console.log( newConfig );
module.exports = newConfig;
