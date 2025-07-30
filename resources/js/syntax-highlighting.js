import hljs from 'highlight.js';

// Import specific languages we need for the blog
import javascript from 'highlight.js/lib/languages/javascript';
import php from 'highlight.js/lib/languages/php';
import bash from 'highlight.js/lib/languages/bash';
import xml from 'highlight.js/lib/languages/xml'; // for HTML
import css from 'highlight.js/lib/languages/css';
import json from 'highlight.js/lib/languages/json';
import yaml from 'highlight.js/lib/languages/yaml';
import sql from 'highlight.js/lib/languages/sql';
import typescript from 'highlight.js/lib/languages/typescript';
import python from 'highlight.js/lib/languages/python';
import java from 'highlight.js/lib/languages/java';
import csharp from 'highlight.js/lib/languages/csharp';
import go from 'highlight.js/lib/languages/go';
import rust from 'highlight.js/lib/languages/rust';

// Register the languages
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('js', javascript);
hljs.registerLanguage('php', php);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('shell', bash);
hljs.registerLanguage('html', xml);
hljs.registerLanguage('css', css);
hljs.registerLanguage('json', json);
hljs.registerLanguage('yaml', yaml);
hljs.registerLanguage('yml', yaml);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('typescript', typescript);
hljs.registerLanguage('ts', typescript);
hljs.registerLanguage('python', python);
hljs.registerLanguage('py', python);
hljs.registerLanguage('java', java);
hljs.registerLanguage('csharp', csharp);
hljs.registerLanguage('cs', csharp);
hljs.registerLanguage('go', go);
hljs.registerLanguage('rust', rust);

// Initialize syntax highlighting when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSyntaxHighlighting();
});

// Function to initialize syntax highlighting
function initializeSyntaxHighlighting() {
    // Highlight all code blocks
    hljs.highlightAll();

    // Process all code blocks
    document.querySelectorAll('pre code').forEach((block) => {
        const pre = block.parentElement;

        // Skip if already processed
        if (pre.classList.contains('hljs-processed')) {
            return;
        }

        // Add wrapper container for better positioning
        if (!pre.parentElement.classList.contains('code-block-container')) {
            const container = document.createElement('div');
            container.className = 'code-block-container';
            pre.parentElement.insertBefore(container, pre);
            container.appendChild(pre);
        }

        // Check if this code block should have line numbers
        const shouldShowNumbers = block.classList.contains('showLineNumbers') ||
                                block.closest('[data-line-numbers]') !== null ||
                                block.closest('.line-numbers') !== null ||
                                true; // Enable line numbers by default

        if (shouldShowNumbers) {
            addLineNumbers(block);
        }

        // Add copy button
        addCopyButton(pre);

        // Mark as processed
        pre.classList.add('hljs-processed');
    });
}

function addLineNumbers(codeBlock) {
    const pre = codeBlock.parentElement;

    // Skip if line numbers already exist
    if (pre.querySelector('.line-numbers-container')) {
        return;
    }

    // Get the actual visible text content (not the innerHTML which includes highlight.js markup)
    const text = codeBlock.textContent || codeBlock.innerText;
    const lines = text.split('\n');

    // Remove empty line at the end if it exists (common with code blocks)
    if (lines[lines.length - 1] === '') {
        lines.pop();
    }

    // Create line numbers container
    const lineNumbersContainer = document.createElement('div');
    lineNumbersContainer.className = 'line-numbers-container';

    // Add line numbers
    for (let i = 1; i <= lines.length; i++) {
        const lineNumber = document.createElement('span');
        lineNumber.className = 'line-number';
        lineNumber.textContent = i;
        lineNumber.setAttribute('data-line', i);
        lineNumbersContainer.appendChild(lineNumber);
    }

    // Style the pre element to accommodate line numbers
    pre.style.position = 'relative';
    // Remove the left padding from pre since we're adding it to the code element
    pre.style.paddingLeft = '0';

    // Insert line numbers at the beginning
    pre.insertBefore(lineNumbersContainer, codeBlock);
}

function addCopyButton(pre) {
    // Skip if copy button already exists
    if (pre.querySelector('.copy-button')) {
        return;
    }

    // Create copy button
    const copyButton = document.createElement('button');
    copyButton.className = 'copy-button';
    copyButton.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        <span class="copy-text">Copy</span>
    `;
    copyButton.setAttribute('title', 'Copy to clipboard');

    // Add click handler
    copyButton.addEventListener('click', async () => {
        const codeBlock = pre.querySelector('code');
        const text = codeBlock.textContent || codeBlock.innerText;

        try {
            await navigator.clipboard.writeText(text);

            // Show success feedback
            copyButton.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
                <span class="copy-text">Copied!</span>
            `;
            copyButton.classList.add('copied');

            // Reset after 2 seconds
            setTimeout(() => {
                copyButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span class="copy-text">Copy</span>
                `;
                copyButton.classList.remove('copied');
            }, 2000);

        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            // Show feedback
            copyButton.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20,6 9,17 4,12"></polyline>
                </svg>
                <span class="copy-text">Copied!</span>
            `;
            copyButton.classList.add('copied');

            setTimeout(() => {
                copyButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span class="copy-text">Copy</span>
                `;
                copyButton.classList.remove('copied');
            }, 2000);
        }
    });

    // Position the button
    pre.style.position = 'relative';
    pre.appendChild(copyButton);
}

// Function to reinitialize highlighting for dynamically loaded content
function reinitializeHighlighting() {
    initializeSyntaxHighlighting();
}

// Export for global use
window.hljs = hljs;
window.reinitializeHighlighting = reinitializeHighlighting;
