const fs = require('fs');
const axios = require('axios');

const UrlPreview = settings => {
  const defaultSettings = {
    // Thumbs
    useDefaultThumb: true,
    defaultThumb: '../assets/defaultThumb.svg',
    // Network
    scheme: 'http',
    headers: {
      'user-agent':
        'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
      Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    },
    // Meta Tags Fields Default References
    metaFields: {
      title: ['og:title', 'twitter:title', 'title'],
      description: ['og:description', 'twitter:description', 'description'],
      sitename: ['og:site_name', 'site_name', 'sitename'],
      thumbnail: ['og:image', 'twitter:image', 'twitter:image:src', 'image'],
      url: ['og:url', 'url']
    }
  };

  function get_tags(html) {
    let metaTags = [];

    const regex = /<\s*meta\s*(?=[^>]*?\b(?:name|http-equiv|property)\s*=\"(.*?)\")[^>]*?\bcontent\=\"(.*?)\"/gim;

    while ((m = regex.exec(html)) !== null) {
      // This is necessary to avoid infinite loops with zero-width matches
      if (m.index === regex.lastIndex) {
        regex.lastIndex++;
      }
      metaTags[m[1].toLowerCase()] = m[2];
    }

    return metaTags;
  }

  function exec_regex(regex, html, defaultReturn = '') {
    return regex.exec(html)[1] || defaultReturn;
  }

  function get_optionals_fields(metaTags = []) {
    let fields = [];
    Object.keys(metaTags).map(kt => {
      Object.keys(defaultSettings['metaFields']).map(kf => {
        if (defaultSettings['metaFields'][kf].indexOf(kt) !== -1) {
          fields[kf] = metaTags[kt];
        }
      });
    });
    return fields;
  }

  async function get_url(url) {
    // Checking URL
    try {
      url = normalize_url(url);
      const checkUrl = new URL(url);
    } catch (err) {
      handle_error('Invalid URL', err);
    }

    // Getting URL
    try {
      // Getting HTML
      const response = await axios.get(url, defaultSettings['headers']);
      const html = response.data;

      // Title
      const title = exec_regex(/<title.*?>\s*(.*)\s*<\/title>/im, html);

      // Domain
      const domain = new URL(url).hostname;

      let result = {
        title,
        domain
      };

      // Getting Meta Tags
      const metaTags = get_tags(html);

      // Getting Default Optionals Fields
      const optsFields = get_optionals_fields(metaTags);

      result = { ...result, ...optsFields };

      // Sources

      // Default Thumb
      if (defaultSettings['useDefaultThumb'] && !result['thumbnail']) {
        result['thumbnail'] = defaultSettings['defaultThumb'];
      }

      // Return
      return result;
    } catch (error) {
      handle_error('Error Getting Url', error);
    }
  }

  function handle_error(message, error) {
    console.error('Error !!!', error); // Save in Log File ?
    throw message;
  }

  function debug_file(content) {
    fs.writeFile('debug.txt', content, function(err) {
      handle_error(err);
    });
  }

  // https://stackoverflow.com/a/11300985
  function normalize_url(url) {
    let newUrl = decodeURIComponent(url);
    newUrl = newUrl.trim().replace(/\s/g, '');
    if (/^(:\/\/)/.test(newUrl)) {
      return `http${newUrl}`;
    }
    if (!/^(f|ht)tps?:\/\//i.test(newUrl)) {
      return `${defaultSettings['scheme']}://${newUrl}`;
    }
    return newUrl;
  }

  return {
    get: get_url
  };
};

module.exports = UrlPreview;
