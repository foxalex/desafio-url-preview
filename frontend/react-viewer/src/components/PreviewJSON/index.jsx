import React from 'react';
import SyntaxHighlighter from 'react-syntax-highlighter';
import zenburn from 'react-syntax-highlighter/dist/esm/styles/hljs/zenburn';

import './styles.css';

const PreviewJSON = ({ preview, isLoading, isTyping }) => {
  return (
    <div className={'preview-json-wrap' + (isLoading ? ' loading' : '')}>
      <h2>Preview JSON</h2>
      <SyntaxHighlighter language="json" style={zenburn}>
        {isLoading || isTyping
          ? JSON.stringify(
              {
                status: isTyping
                  ? 'typing...'
                  : isLoading
                  ? 'loading...'
                  : 'idle'
              },
              null,
              2
            )
          : JSON.stringify(preview, null, 2)}
      </SyntaxHighlighter>
    </div>
  );
};

export default PreviewJSON;
