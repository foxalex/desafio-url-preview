import axios from 'axios';

export default async function getPreview(url) {
  try {
    return await axios.post(process.env.REACT_APP_API_URL, { url });
  } catch (err) {
    console.warn('Error Loading Api');
    return err.response;
  }
}
