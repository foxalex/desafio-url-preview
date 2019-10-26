import React, { useState } from 'react';

import './styles.css';
import usePreviewer from '../../hooks/usePreviewer';

import InputUrl from '../InputUrl';
import Preview from '../Preview';
import PreviewJSON from '../PreviewJSON';
import ErrorPreview from '../ErrorPreview';

const App = () => {
  const [typing, setTyping] = useState(false);
  const [initiated, setInitiated] = useState(false);
  const [currentUrl, setCurrentUrl] = useState('');

  const [state, setUrl] = usePreviewer();

  const { preview, loading, error, errorMessage } = state;

  const handleChangeInput = url => {
    setInitiated(true);
    setCurrentUrl(url);
  };

  const handleTyping = typing => {
    if (!typing && initiated) {
      if (currentUrl.length > 4) {
        console.log('doing api');
        setUrl(currentUrl);
      }
    }
    setTyping(typing);
  };

  return (
    <div className="app">
      <img
        className="logo-wrap"
        src="assets/monkey-url.svg"
        alt="Url Preview"
      />
      <h1>Url Preview</h1>
      <InputUrl
        onChange={handleChangeInput}
        handleTyping={handleTyping}
        isLoading={loading}
      />
      {initiated && (
        <div className="previews-wrap">
          {error ? (
            <ErrorPreview message={errorMessage} />
          ) : (
            <Preview preview={preview} isLoading={loading || typing} />
          )}
          <PreviewJSON
            preview={preview}
            isLoading={loading}
            isTyping={typing}
          />
        </div>
      )}

      {/* <p>{JSON.stringify(state)}</p> */}
    </div>
  );
};

export default App;
