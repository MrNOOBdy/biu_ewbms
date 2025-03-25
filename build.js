import { readFileSync, writeFileSync } from 'fs';
import CleanCSS from 'clean-css';

const cssFiles = [
    'public/css/general.css',
    'public/css/loading.css',
    'public/css/modal.css',
    'public/css/navi_bar.css',
    'public/css/tab_header.css',
    'public/css/table.css',
    'public/css/tabs.css',
    'public/css/dashboard.css',
];

const minifier = new CleanCSS({
    level: {
        1: {
            specialComments: 0,
            removeEmpty: true
        }
    }
});

try {
    const css = cssFiles
        .map(file => readFileSync(file, 'utf8'))
        .join('\n');

    const minified = minifier.minify(css);
    writeFileSync('public/css/main.min.css', minified.styles);
    
    writeFileSync('public/css/variables.css', 
        readFileSync('public/css/variables.css', 'utf8'));
        
    console.log('CSS minification complete');
} catch (error) {
    console.error('Error:', error);
    process.exit(1);
}
