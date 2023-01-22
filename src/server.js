const express = require('express')
const app = express()
let visits = 0
app.get('/', (req, res) => {
    visits++
    res.sendFile(__dirname + '/index.html', {
        headers: {
            'Content-Type': 'text/html'
        }
    })
})
app.get('/visits', (req, res) => {
    res.json({ visits })
})
app.listen(process.env.PORT || 3000, () => {
    console.log('Server started on port 3000')
})