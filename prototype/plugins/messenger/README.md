JQuery-Chat
===========

It's a plugin (@TO-DO)

Description
===========

Chat interfaces, a lot of option (@TO-DO)

How To
===========

Just do this:

$("div.your-selector").im({
  jid: "username",
  password: "password",
  url: "To connect"
});

You can also check the sample in sample/

Other Options
===========

defaultStatus
===========

By default this options is set by null(default value), this means that you will be "online" when the chat connect to the server.

afterMessage and afterIq
===========

You can pass functions to save the messages sent and received by javascript.

contactList
===========

Load the contact list using and array, its more quicky than wait the plugin connect to the server and get the presences, and you will see the offline people too,
by default this option is passed like this: 

[
  {
    "from" : "jid of the contact"
  },
  {
  ...
  }
]

contactNameIndex
===========

This option is the "from"(default value) of the "jid of the contact" above.

debug
===========

Logs somethings that can be useful.

soundPath
===========

This is the path of the sound, you choose, if you don't, will just use the navigator url as default.

soundName
===========

This is the name of the sound, if you want to put another sound, we have 2 by Default (icq and pop), but you are free to change.
//Note: You need a .ogg and a .mp3 to play the sound in every browser.

emotions
===========

This is the emotions regex and css classes, we have some default emotion if you wanna add follow this sample below:

[
  	{
  		emotion: /:\)/g, // regex
  		emotionClass: "smile" //class
  	},
]

in css below:

.emotion.smile{
  background-image: url("images/emoticon_smile.png");
}

