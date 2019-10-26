const cors = require('cors');
const express = require('express');
const app = express();

const PORT = 3000;

const urlPreview = require('./UrlPreview')(); // ([settings])

//const res = urlPreview.get('http://globo.com');

app.use(express.json());
app.use(cors());

app.get('/', async (req, res) => {
  try {
    res.json(await urlPreview.get('http://globo.com'));
  } catch (err) {
    res.status(500).json({ error: true, error_message: err });
  }
});

app.post('/', async (req, res) => {
  try {
    res.json(await urlPreview.get(req.body.url));
  } catch (err) {
    res.status(500).json({ error: true, error_message: err });
  }
});

app.listen(PORT, () => {
  console.log('Api Server listening on port ' + PORT + '!');
});
