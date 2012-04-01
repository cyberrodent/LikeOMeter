/*jslint browser: true  */
/*global FB, $, tmpl, client_name, pv, alert */

/*
 * Likeometer
 * ==========
 *
 * Initialization Cycle
 * --------------------
 *
 * init is exported and calling init() will start the likeometer.
 *
 * init() will ask the graph a little bit about the user (me) and callback to init_a
 *
 * init_a asks for the uid of all me's friends in a random orger and calls back build
 *
 * build takes that list of friend uids and in chunks asks the graph for all the things
 * those uids like. This callsback to collate
 *
 * collate builds the "collikes" and "things" array. collate keeps track to make sure we
 * get called back for all your friends and then calls show_top_likes()
 *
 * show_top_likes draws the list of things that me's friends like. 
 */

"use strict";

var Likeometer = function () {
    this.friends = [];
    this.token = false;
    this.graph = false;
    this.user = {};

    var self = this,
        likes = {},             // hash of things user likes, keyd by uid: uid => likes
        things = {},            // data about each liked thing; keyd by thing_id: id => data
        collikes = {},          // collate likes in here liked_things_id => [ uids of friends who like this ]
        like_counts = {},       // keyed by thing_id, how many friends like it
        all_friends = [],       // keyed by id, the name of each friend
        arrived = [],           // track which users like data has arrived from the graph
        like_count_keys = [],   // sorted array of the thing_ids like
        started = false,        // if we've been started - prevent restarting - as yet unimplemented
        processed = false,      // keep certain functions from rendering if processing is not yet complete
        rThings = {},           // thing data needed to render a row of liked thing
        scroll_point = 0,       // track how far down in pixels we've infinite scrolled
        scrolled_pages = 0,     // track how many pages we've loaded
        page_size = 5,          // how many items to load on each call - attach to infinite scroll
        lom_debug = false,      // toggle on or off debugging
        how_many_friends_i_have = 0, // self documenting

        set_status_line = function (message) {
            // assign text message to the status bar
            $("#statusline").text(message);
        },

        count_likes = function () {
            var count, i;
            count = 0;
            for (i in likes) {
                if (likes.hasOwnProperty(i)) {
                    count = count + 1;
                }
            }
            return count;
        },

        compare_collikes = function (a, b) {
            var ret = null;
            // sorting function to sort things by how many friends like them
            if (collikes[a].length > collikes[b].length) {
                ret = -1;
            } else if (collikes[a].length === collikes[b].length) {
                ret = 0;
            } else {
                ret = 1;
            }
            return ret;
        },

        do_common = function () {
            // This makes a list of friends who have likes in common with you
            var n, i, friend_likes, t,
                my_likes = likes[self.uid],
                my_like_ids = [],
                commons = {};
            for (i = 0; i < my_likes.length; i = i + 1) {
                my_like_ids.push(my_likes[i].id);
            }
            set_status_line("Initializing: Finding common interests");

            for (n in all_friends) {
                if (all_friends.hasOwnProperty(n)) {
                    if (n !== self.uid) {
                        friend_likes = likes[n];
                        for (i = 0; i < friend_likes.length; i = i + 1) {
                            if (my_like_ids.indexOf(friend_likes[i].id) > 0) {
                                if (typeof commons[friend_likes[i].id] === "undefined") {
                                    commons[friend_likes[i].id] = [n];
                                } else {
                                    commons[friend_likes[i].id].push(n);
                                }
                            }
                        }
                    }
                }
            }
            for (t in commons) {
                if (commons.hasOwnProperty(t)) {
                    $("#commonlikes").append("<div>" + things[t].name + " Liked by you and these " + commons[t].length + " friends.</div>");
                }
            }
        },

        find_thing_with_two = function () {
            // this is used as part of creating the message to write on the users wall
            // it picks a random thing that exactly 2 of your friends like
            var thing = 0, i, ret;
            for (i in like_counts) {
                if (like_counts[i] === 2) {
                    thing = i;
                    break;
                }
            }
            if (thing === 0) {
                ret = like_counts[Math.floor(Math.random() * like_counts.length)];
            } else {
                ret = thing;
            }
            return ret;
        },

        if_not_already_announced = function (callback, param) {
            // look on my own wall and see if likeometer published recently
            // if not, then call callback with params
            var i;

            FB.api("/me/feed", function (res) {
                if (typeof res.data  === 'object') {
                    for (i = 0; i < res.data.length; i = i + 1) {
                        try {
                            // client_name is defined in our setup file
                            if (res.data[i].application.namespace === client_name) {
                                //"ns_enilemit_local") {
                                return false;
                            }
                        } catch (e) {
                            $.noop();
                        }
                    }
                } else {
                    return false;
                }
                callback(param);
            });
        },

        announce_on_wall = function () {
            // write a message on the users wall
            var obj = {},
                use_ui = 1,
                first = things[like_count_keys[0]],
                second = things[like_count_keys[1]],
                third = things[like_count_keys[2]],
                lucky = find_thing_with_two(),
                rand = things[lucky],
                o = "More of my friends like " + first.name + ", " + second.name + " and " + third.name + " than anything else. " + "But only 2 of them like \"" + rand.name + "\".",
                params = {};
            params.message = o;
            params.name = 'Like-o-Meter';
            params.description = 'What your friends like';
            params.link = 'https://apps.facebook.com/like_o_meter/';

            if (use_ui === 1) {
                // calling the API ...
                obj = {
                    method: 'feed',
                    link: 'https://apps.facebook.com/like_o_meter/',
                    // FIXME:
                    // picture: 'https://enilemit.home/images/lom.png',
                    picture: 'https://young-autumn-6232.herokuapp.com/images/lom.png',
                    name: 'Like-O-Meter',
                    caption: o,
                    description: 'See what stuff your friends like.'
                };
                FB.ui(obj,  function (response) {
                    $.get('/likeometer/tographite.php', {
                        'key' : 'share_on_wall',
                        'val' :  1
                    });
                });
            } else {
                // use graph api
                FB.api('/me/feed', 'post', params, function (res) {
                    if (!res || res.error) {
                        alert('error' + res.error.message);
                    } else {
                        //alert("published to stream");
                        $.noop();
                    }
                });
            }
        },

        switch_page = function (to_show) {
            // show and hide divs to make it feel like switching to another "page"
            $("div.loading").hide();
            $("div#about").hide();
            $("div#friendslikes").hide();
            $("div#melikes").hide();
            $("div#common").hide();

            $("nav a").removeClass("here");

            if (to_show) {
                $('div' + to_show).show();
                $("nav").find(to_show).addClass("here");
            }
        },

        //
        // these "*_action" functions handle what "page"
        // is being shown.
        //
        flikes_action = function () {
            var uid = self.uid;
            set_status_line("What your friend's like");
            switch_page("#friendslikes");
        },

        home_action = function () {
            switch_page("#about");
            set_status_line("Welcome");
        },

        you_action = function () {
            $.get('/likeometer/tographite.php', {
                'key' : 'melikes',
                'val' : 1
            });
            switch_page("#melikes");
        },

        common_action = function () {
            set_status_line("What you and your friends have in common");
            switch_page("#common");
        },
        //
        // this ends our section of *_action definitions
        //

        draw_liked_thing_row = function (res) {
            var data, d;
            data = rThings[res.id];
            if (typeof data  === "undefined") {
                // console.log("got back undefined data");
                // console.log(res);
                //
                // FIXME Need somewhere to put errors like this
                // FIXME Need someone to look at error logs from time to time
                return;
            }
            if (res.link) {
                data.link = res.link;
            }
            data.friend_name = all_friends;
            data.picture = res.picture;
            d = tmpl("ltr_tpl", data);
            $("#ltr" + res.id).replaceWith(d);
            FB.XFBML.parse(document.getElementById("h2" + res.id));
        },

        show_top_likes = function () {
            // shows a set of the users friends top likes
            // the size of the set is determinied by scroll_point
            // and page_size.
            //
            // Now with infinite scrolling
            var i, limit, thing_id, dataObject, d, go;
            if (!processed) {
                return;
            }

            if (scroll_point === 0) {
                page_size += page_size;
            }
            limit = scroll_point + page_size;

            // track how far down someone scrolls
            scrolled_pages = scrolled_pages + 1;
            $.get('/likeometer/tographite.php', {
                'key' : 'page',
                'val' :  scrolled_pages
            });
            // collikes are things keyed by thing_id
            // like_count_keys is an index of things sorted by how many of me's friends like it
            for (i = scroll_point;  i < limit; i = i + 1) {
                if (collikes[like_count_keys[i]].length > 1) {
                    thing_id = like_count_keys[i];
                    dataObject = {};
                    dataObject.thing_id = thing_id;
                    dataObject.token = self.token;
                    dataObject.how_many_friends = collikes[thing_id].length;
                    dataObject.things_name = things[thing_id].name;
                    dataObject.things_category = things[thing_id].category;
                    dataObject.aLikers = collikes[thing_id];
                    dataObject.friend_name = null;
                    dataObject.link = '';

                    // stash it where a callback and template can access it
                    rThings[thing_id] = dataObject;

                    // render a placeholder template to handle keeping things in
                    // ranked order. ltrph is Liked Thing Row Place Holder
                    d = tmpl("ltrph_tpl", dataObject);
                    $("div#friendslikes").append(d);

                    // on callback swap DOM with the placeholder
                    FB.api('/' + thing_id + "?fields=link,username,id", draw_liked_thing_row);
                }
            }

            if (scroll_point === 0) {// first time only
                $("#statusline").hide();
                $('nav a#friendslikes').click(flikes_action);
                $('nav a#about').click(home_action);
                // this command would enable click to the "common likes" page
                $('nav a#common').click(common_action);
                // this would enable the "your likes" page
                $('nav a#melikes').click(you_action);
                $('nav a#share').click(announce_on_wall);
                FB.XFBML.parse(document.getElementById("header"));

                $("nav").show();
                switch_page("#friendslikes");

                FB.Event.subscribe('edge.create', function (response) {
                    var params;
                    $.get('/likeometer/tographite.php', {
                        'key' : 'newfound_like',
                        'val' : 1
                    });

                    // write to the open graph when user finds a new like
                    params = {'access_token' : self.token, 'website' : response, 'profile' : response};
                    $.post('https://graph.facebook.com/me/like_o_meter:discover', params, function (res) {
                        $.noop();
                    });

                });
                FB.Event.subscribe('edge.remove', function (response) {
                    $.get('/likeometer/tographite.php', {
                        'key' : 'lost_like',
                        'val' : 1
                    });
                });
                // write on the users wall if we haven't aleady done so recently
                go = if_not_already_announced(announce_on_wall);

                // attach scroll handler
                $(document).scroll(function () {
                    var doc_h, win_h, st, data, d;
                    doc_h = $(document).height();
                    win_h = $(window).height();
                    st = $(window).scrollTop();
                    // this should kick in when we get
                    // 1 scrollbar's height above the
                    // bottom of the screen.
                    // FIXME: be more sure these are ints before
                    // using + on them ?
                    if (st + win_h + win_h > doc_h) {
                        show_top_likes();
                    }
                    if (lom_debug) { // shows some scrolling debug info
                        data = {
                            'doc_height' : doc_h,
                            'scrolltop' : $(window).scrollTop() + $(window).height(),
                            'scroll_bar_height' : (st + win_h + win_h > doc_h) ? 'LOAD NOW!' : ''
                            // debug info if you want it
                            //'crap' : " DocH: " + $(document).height()  +
                            //  " WinH:" + $(window).height() + " ~~~>> " +
                            //  ( $(window).height() / $(document).height() ) * $(window).height()
                        };
                        d = tmpl("debug_tpl", data);
                        $("#debug").replaceWith(d);
                    } // end scrolling debug block
                });
                // Metrics gathering via facebook
                FB.Canvas.setDoneLoading(function (result) {
                    $.get('/likeometer/tographite.php', {
                        'key' : 'load_time_ms',
                        'val' : result.time_delta_ms
                    });
                });
            }
            /*
            // Draw a button at the bottom if there are more to get
            // This is a fallback if the infinite scroll didn't work
            */
            $("#more").remove();
            if (limit < Object.size(collikes)) {
                $("div#friendslikes").append("<div id='more'>Click to see more.</div>");
                $("#more").click(show_top_likes);
            }
            scroll_point = limit; // ready for more ...
            if (lom_debug) { // debug
                $("#debug").append("<p>H" + $(document).height() + "</p>");
                $("#debug").append("<p>S" + $(window).scrollTop() + "</p>");
            }
        },

        got_my_likes = function () {
            //
            // Categorize your likes by category
            //   currently beta in the application
            //
            var my_cats, my_cat_keys, sort_cat_counts, like, i, my_likes,
                key, o, how_many, j, thing_id, thing_name, tpl_data;
            $("div#melikes").replaceWith($("<div id='melikes'>Here are the things you like grouped by category.</div>"));
            my_likes = likes[self.uid];

            my_cats = {};
            my_cat_keys = [];
            sort_cat_counts = function (a, b) {
                var ret = null;
                if (my_cats[a].length < my_cats[b].length) {
                    ret = 1;
                } else if (my_cats[a].length ===  my_cats[b].length) {
                    ret = 0;
                } else {
                    ret = -1;
                }
                return ret;
            };
            for (like in my_likes) {
                if (my_likes.hasOwnProperty(like)) {
                    if (typeof my_cats[my_likes[like].category] === 'undefined') {
                        my_cats[my_likes[like].category] = [my_likes[like].id];
                        my_cat_keys.push(my_likes[like].category);
                    } else {
                        my_cats[my_likes[like].category].push(my_likes[like].id);
                    }
                }
            }
            my_cat_keys.sort(sort_cat_counts);
            for (i in my_cat_keys) {
                if (my_cat_keys.hasOwnProperty(i)) {
                    key = my_cat_keys[i];
                    o = "<div><h3>" + key + "</h3>";
                    how_many =  my_cats[key].length;
                    o += "You like " + how_many + " things from \"" + key + "\"<br />";

                    for (j in my_cats[key]) {
                        if (my_cats[key].hasOwnProperty(j)) {
                            thing_id = my_cats[key][j];
                            thing_name = (typeof things[thing_id] !== 'undefined') ? things[thing_id].name : 'huh';
                            tpl_data = {
                                'token' : self.token,
                                'thing_id' : thing_id,
                                'thing_name' : thing_name
                            };
                            o += tmpl("my_cat_tpl", tpl_data);
                        }
                    }
                    o += "</div>";
                    $("div#melikes").append(o);
                }
            }
            $('a#melikes').click(you_action);
        },

        make_things = function (data) {
            var i;
            for (i in data) {
                if (data.hasOwnProperty(i)) {
                    things[data[i].id] = data[i];
                }
            }
        },

        collate = function (res) {
            // Collates friend likes by liked thing:
            // handles batches of input coming back async
            // build things and collikes arrays
            // sets processing to true when this is done
            // kicks off show_top_likes when all done

            var friend_id, flikes, i, j, thing_id;

            set_status_line("Collating");
            if (!res || res.error) {
                set_status_line("Something went wrong " + res.error.message);
                return;
            }
            if (res.error_code) {
                set_status_line("Something went wrong. Error code: " + res.error_code);
                return;
            }
            if (typeof res.error !== 'undefined') {
                set_status_line(res.error.type + " Error: " + res.error.message);
                return;
            }

            for (friend_id in res) {
                if (res.hasOwnProperty(friend_id)) {
                    arrived.push(friend_id);
                    flikes = res[friend_id].data;
                    likes[friend_id] = res[friend_id].data;
                    if (friend_id === self.uid) {
                        got_my_likes();
                    }
                    set_status_line("Collated " + Object.size(collikes) + " things from " + arrived.length + " friends.");
                    for (j = 0; j < flikes.length; j = j + 1) {
                        thing_id = flikes[j].id;
                        things[thing_id] = flikes[j];
                        if (typeof collikes[thing_id] !== 'undefined') {
                            collikes[thing_id].push(friend_id);
                        } else {
                            collikes[thing_id] = [friend_id];
                        }
                    }
                }
            }

            // if we are last then we do this
            if (arrived.length >= how_many_friends_i_have) {
                set_status_line("Sorting.....");
                for (i in collikes) {
                    if (collikes.hasOwnProperty(i)) {
                        like_counts[i] = collikes[i].length;
                        like_count_keys.push(i);
                    }
                }
                like_count_keys.sort(compare_collikes);
                processed = true;
                show_top_likes();
                // wire this off for now
                // show_distribution();
            }
        },

        createLayout = function (data, points, max) {
            // part of the histogram
            var y, vis,
                w = 620,
                h = 200,
                x = pv.Scale.linear(0, max).range(0, w),
                bins = pv.histogram(data).bins(x.ticks(30));
            y = pv.Scale.root(0, points).range(0, h).power(3);
            vis = new pv.Panel()
                .width(w)
                .height(h)
                .margin(40);
            vis.add(pv.Bar)
                .data(bins)
                .bottom(0)
                .left(function (d) {return x(d.x); })
                .width(function (d) {return x(d.dx); })
                .height(function (d) {return y(d.y); })
                .fillStyle("#f36")
                .strokeStyle("rgba(255,255,255, 1)")
                .lineWidth(1)
                .antialias(false);
            vis.add(pv.Rule)
                .data(y.ticks(5))
                .bottom(y)
                .left(0)
                .strokeStyle("#333")
                .anchor("left").add(pv.Label)
                .text(y.tickFormat);
            vis.add(pv.Rule)
                .data(x.ticks())
                .strokeStyle("#333")
                .left(x)
                .bottom(-5)
                .height(5)
                .anchor("bottom").add(pv.Label)
                .text(x.tickFormat);
            vis.add(pv.Rule)
                .bottom(0);
            vis.render();
        },

        show_distribution = function () {
            // show some sort of histogram showing the long long tail
            var like_count_lengths, i, h, max, dist, DIST, di;

            like_count_lengths = [];
            for (i = 0; i < like_count_keys.length; i = i + 1) {
                h = collikes[like_count_keys[i]].length;
                like_count_lengths.push(h);
            }
            max = 0;
            dist = {};
            for (i = like_count_lengths.length; i >= 0; i = i - 1) {
                if (like_count_lengths[i] > max) {
                    max = like_count_lengths[i];
                }
                if (typeof dist[like_count_lengths[i]] === 'undefined') {
                    dist[like_count_lengths[i]] = 1;
                } else {
                    dist[like_count_lengths[i]] = dist[like_count_lengths[i]] + 1;
                }
            }
            DIST = [];
            for (di in dist) {
                if (dist.hasOwnProperty(di)) {
                    if (di !== 'undefined') {
                        DIST.push({
                            'dx': 1,
                            'x' : dist[di],
                            'y' : di
                        });
                    }
                }
            }
            //var data = {
            //  'like_count_lengths' : like_count_lengths,
            //};
            // var d = tmpl("histogram", data);
            // $("#friendslikes").before(d);

            $("div#friendslikes").append("<div id='vis'></div>");
            createLayout(like_count_lengths, like_count_lengths.length, 1 + max);
        },

        call_collate = function (res) {
            collate(res);
        },

        build = function (res) {
            // gets the list of friends that kicks off the whole show
            var friends, fids, f, i, message, chunk_size, c, chunk;
            if (typeof res.error !== 'undefined') {
                set_status_line(res.error.type + " Error: " + res.error.message);
                return;
            }
            set_status_line("Building query");
            friends = res;
            fids = ''; // string of comma separated friend ids
            f = [];
            for (i = 0; i < friends.length; i = i + 1) {
                f.push(friends[i].uid);
                all_friends[friends[i].uid] = friends[i].name;
            }
            // add me to my set of frineds
            f.push(self.user.id);

            how_many_friends_i_have = Object.size(all_friends);

            // Doesn't work for large result sets. You get a network error.
            // So we need to chunk.
            message = "Asking Facebook what your friends like. ";
            set_status_line(message);

            chunk_size = 8; // how many friend likes to ask for in a batch?
            c = 0; // to keep track of how many chunks we've fetched
            while (f.length) {
                if (c > 9999) {
                    // stop runaway
                    break;
                }
                c = c + 1;
                chunk = f.splice(0, chunk_size);
                fids = chunk.join(','); // fid ~ "Friends' ids"
                FB.api("/likes?ids=" + fids, call_collate);
            }
        },

        init_a = function (token, uid) {
            self.token = token;
            self.uid = uid;

            // get data via FB.Data.query
            var friends_id = FB.Data.query(
                'select uid, name from user where uid in (' +
                    'select uid2 from friend ' +
                    'where uid1=me() order by rand() ' + ')'
            );
            friends_id.wait(function (rows) {
                build(rows);
            });
        },

        init = function (token, uid) {
            // set_status_line("Hello.  We're just starting");
            var ts, me;
            self.token = token;
            self.uid = uid;
            ts = Math.round((new Date()).getTime() / 1000);
            if (!started) {
                started = true;
                switch_page(".loading");
                me = FB.Data.query('select name, uid from user where uid={0}', uid);
                me.wait(function (rows) {
                    self.user = rows[0];
                    self.user.id = self.user.uid;
                    init_a(token, uid);
                });
            }
        };

    return {
        init : init
    };
};
