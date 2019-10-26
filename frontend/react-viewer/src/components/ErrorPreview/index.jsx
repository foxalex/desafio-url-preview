import React from 'react';

import './styles.css';

const ErrorPreview = ({ message }) => (
  <div className="error-preview-wrap">
    <span>🙈</span>
    <br />
    Ops
    <br />
    Erro ao carregar Preview.
    {message && <br />}
    {message}
  </div>
);

export default ErrorPreview;
