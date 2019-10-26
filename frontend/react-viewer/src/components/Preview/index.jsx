import React from 'react';

import './styles.css';

const PreviewLoading = () => (
  <div className="preview-loading-wrap">
    <div className="image-wrap"></div>
    <div className="text-wrap">
      <span className="title-fake"></span>
      <div className="description-fake">
        {Array.from({ length: 3 }).map((r, i) => (
          <span key={i}></span>
        ))}
      </div>
    </div>
  </div>
);

const PreviewResult = ({ preview }) => {
  const { thumbnail, domain, description, title } = preview;
  return (
    <div className="preview-result-wrap">
      <div className="thumb-wrap">
        <img src={thumbnail} alt="Thumbnail Preview" />
      </div>
      <div className="text-wrap">
        <h3>{title}</h3>
        <p className="sitename">{domain}</p>
        <p className="description">{description}</p>
      </div>
    </div>
  );
};

const Preview = ({ preview, isLoading }) => {
  return (
    <div className="preview-wrap">
      <h2>Preview</h2>
      {isLoading ? <PreviewLoading /> : <PreviewResult preview={preview} />}
    </div>
  );
};

export default Preview;
