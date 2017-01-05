'use strict';
var Alexa = require('alexa-sdk');
var config = require('./config');
var request = require('request');
var http = require('http');

exports.handler = function(event, context, callback) {
    var alexa = Alexa.handler(event, context);
    alexa.APP_ID = config.appid;
    alexa.registerHandlers(handlers);
    alexa.execute();
};

var handlers = {
    'TurnOnTV': function () {
        var that = this;
        sendCommand("/livebox/turnon", null, function ResponseCallback(res) {
            console.log(res);
            that.emit(':tell', "Livebox TV on");
            that.context.succeed();
        });
    },
    'TurnOffTV': function () {
        var that = this;
        sendCommand("/livebox/turnoff", null, function ResponseCallback(res) {
            console.log(res);
            that.emit(':tell', "Livebox TV off");
            that.context.succeed();
        });
    },
    'GetTVState': function () {
        // TODO
    },
    'ChangeChannel': function () {
        var that = this;
        sendCommand("/livebox/channel/"+that.event.request.intent.slots.Channel.value, null, function ResponseCallback(res) {
            console.log(res);
            that.emit(':tell', "Ok");
            that.context.succeed();
        });
    },
    'ChangeVolume': function () {
        var that = this;
        sendCommand("/livebox/volume/"+that.event.request.intent.slots.Direction.value+"/"+that.event.request.intent.slots.Value.value, null, function ResponseCallback(res) {
            console.log(res);
            that.emit(':tell', "Ok");
            that.context.succeed();
        });
    }
};

function sendCommand(path, body, callback) {
    var opt = {
        host: config.host,
        port: config.port,
        path: path,
        method: 'GET'
    };

    var req = http.request(opt, function(res) {
        res.setEncoding('utf8');
        res.on('data', function (chunk) {
            console.log('Response to ' + opt.path + ': ' + chunk);
            callback(chunk);
        });
    });

    if (body) req.write(body);
    req.end();
};