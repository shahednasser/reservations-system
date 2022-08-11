var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);
var axios = require('axios');

server.listen(6001);

io.on('connection', function (socket) {
  console.log("connected");

  socket.on('notification.pushed', function(data){
    console.log("received", data);
  })
});
