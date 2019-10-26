import { useState, useEffect } from 'react';

import getPreview from '../services/api';

export default function usePreviewer() {
  const initialPreview = {
    url: '',
    domain: '',
    sitename: '',
    thumbnail: '',
    title: '',
    description: ''
  };

  const initialState = {
    error: false,
    errorMessage: '',
    loading: false,
    preview: initialPreview
  };

  const [state, setState] = useState(initialState);
  const [url, setUrl] = useState('');

  useEffect(() => {
    const handleError = msg => {
      setState(s => ({
        ...s,
        loading: false,
        error: true,
        errorMessage: msg
      }));
    };

    const loadPreview = async () => {
      try {
        setState(s => ({ ...initialState, loading: true }));
        const res = await getPreview(url);
        if (res && res.data) {
          if (res.data.error) {
            handleError(res.data.error_message);
          }
          if (res.data) {
            setState(s => ({ ...s, loading: false, preview: res.data }));
            return true;
          }
        }
        handleError('Error Loading Preview Request');
      } catch (err) {
        console.log(err);
        handleError('Error Loading Preview');
      }
    };

    if (url.length > 3) {
      loadPreview();
    }
  }, [url]);

  return [state, setUrl];
}

// Exemplo se eu fosse utilizar Context
/*
import React, {useState, useEffect, useMemo, useReducer, createContext} from 'react';

import getPreview from '../services/api';

const initialPreview = {
  url: '',
  domain: '',
  sitename: '',
  thumbnail: '',
  title: '',
  description: '',
}

const initialState = {
  error: false,
  errorMessage: '',
  loading: false,
  preview : initialPreview
}

const reducer = (state, action) => {
  switch (action.type) {
    case 'loading':
      return { ...state, loading: true, error: false, errorMessage: '' };
    case 'loaded':
      return { ...state, loading: false };
    case 'update':
      return {
        ...state,
        loading: false,
        ...action.payload
      };
    case 'error':
      return {
        ...state,
        loading: false,
        error: true,
        errorMessage: action.payload
      };
    default:
      return state;
  }
};

const PreviewerContext = createContext({});

export const PreviewerProvider = (props) => {
  
  const [state, dispatch] = useReducer(reducer, initialState);

  const contextValue = useMemo(() => {
    return { state, dispatch };
  }, [state, dispatch]);

  return (
    <PreviewerContext.Provider value={contextValue}>
      {props.children}
    </PreviewerContext.Provider>
  );
}

export const PreviewerConsumer = PreviewerContext.Consumer;
export default PreviewerContext;

*/
