### Lyrical ♪

A little PHP script that fetches plaintext lyrics from [genius.com](https://genius.com/)
and dumps them to the terminal.

Back when I used to use foobar2000, (in a deep, dark past where I used Windows...),
I loved having a lyrics panel plugin. Lyrics are a large part of what I enjoy in songs.

My music player of choice these days is [cmus](https://cmus.github.io/). I haven't found
a convenient lyrics program that runs in the terminal yet, so I made my own. Though as yet
its features are limited.

### Dependencies 

- php > 8
- the `composer` package manager

### How to use

1. Clone this repo to a location of your choice.
2. Run `composer install` (This script depends on `guzzle` for HTTP requests, nothing else)
2. Create a genius account.
3. Create an API client: <https://genius.com/api-clients>. Just fill in some random
   things, it doesn't matter for this script.
4. Generate an access token for your "client", and save it to a file named `.token` in the
   root of this directory. Maybe make the file only readable and writeable by your own
   user.

```sh
php src/index.php --artist 'Windmills' --title 'True Natural'
```

`--artist` is optional but recommended since the script merely grabs the top search
result, so improving search result accuracy is important.

I use a bash script to get the currently playing song in cmus and to save the lyrics to
a file. For inspiration, see <https://github.com/raymon-roos/scripts>. That way
I can use [fd](https://github.com/sharkdp/fd) for searching files, rather than
implementing my own super slow, single-threaded, PHP file searching algorithm.
This whole thing could be a bash script, if you have a good way to parse HTML…

### Eventual goals

Eventually I might implement this in a real language. Maybe combine multiple sources for
lyrics. Support multiple music players. Add time cues (karaoke mode). And make the program
interactive to allow for more advanced features. We'll see…
