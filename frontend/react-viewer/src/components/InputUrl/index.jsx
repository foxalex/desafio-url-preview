import React, { useState, useEffect, useRef, useCallback } from 'react';

import './styles.css';

const AWAIT_TIME = 2000;
const TICK_TIME = 500;

const InputUrl = ({ onChange, handleTyping, isLoading }) => {
  const initialState = {
    url: '',
    typing: false,
    currentTime: 0
  };

  const [state, setState] = useState(initialState);
  const { url, typing } = state;

  useEffect(() => {
    handleTyping(typing);
  }, [typing]);

  const checkingTyping = useCallback(() => {
    setState(s => {
      if (s.typing) {
        const newCurrentTime = s.currentTime - 500;
        if (newCurrentTime <= 0) {
          return {
            ...s,
            typing: false,
            currentTime: AWAIT_TIME
          };
        } else {
          return {
            ...s,
            currentTime: newCurrentTime
          };
        }
      }
      return s;
    });
  }, []);

  useEffect(() => {
    const intervalId = setInterval(checkingTyping, TICK_TIME);
    return () => {
      clearInterval(intervalId);
    };
  }, []);

  const handleChange = e => {
    const value = e.target.value;
    setState(s => ({
      ...s,
      url: value,
      typing: true,
      currentTime: AWAIT_TIME
    }));

    onChange(value);
  };

  return (
    <div className="input-url-wrap">
      <input
        type="text"
        onChange={handleChange}
        value={url}
        placeholder="https://github.com/codigofalado/desafio333"
      />
      <p className="feedback">
        {typing
          ? '👨‍💻 digitando...'
          : isLoading
          ? '⏳ carregando preview...'
          : 'entre com a Url que deseja a Preview'}
      </p>
    </div>
  );
};

export default InputUrl;
