const express = require('express')
const ServerlessHttp = require('serverless-http')
const app = express()
const router = express.Router()
let visits = 0
router.get('/', (req, res) => {
    visits++
    res.sendFile(__dirname + '/index.html', {
        headers: {
            'Content-Type': 'text/html'
        }
    })
})
router.get('/visits', (req, res) => {
    res.json({ visits })
})

app.use("/.netlify/functions/api", router)
module.exports.handler = ServerlessHttp(app)