import { writeFileSync, existsSync } from 'fs';

// Test file writing
try {
    // Create a simple CSS content
    const testContent = '/* Test CSS */\nbody { margin: 0; }';
    
    // Try to write to the file
    writeFileSync('public/css/main.min.css', testContent);
    
    // Verify file exists
    if (existsSync('public/css/main.min.css')) {
        console.log('Success: main.min.css was created!');
    }
} catch (error) {
    console.error('Error creating file:', error);
}
