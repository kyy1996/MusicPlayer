/**
 * Created by user on 2016/7/16.
 */
window.AudioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext || window.msAudioContext;
if (!window.AudioContext) {
    throw new Error("Audio Context not supported!");
}
function formatTime(sec) {
    var min = Math.floor(sec / 60);
    var secs = parseInt(sec % 60);
    if (secs < 10) secs = '0' + secs;

    return min + ":" + secs;
}
function Player() {
    var timer = 0;
    var Audio = document.getElementById("audio-source");
    this.AudioContext = new window.AudioContext();
    var source = this.AudioContext.createMediaElementSource(Audio);
    var gain = this.AudioContext.createGain();
    gain.value = 3;
    source.connect(gain);
    gain.connect(this.AudioContext.destination);

    Audio.loop = false;

    this.loop = 2; //0-单曲播放, 1-单曲循环, 2-列表播放, 3-列表循环， 4-随机播放
    var _this = this;

    this.oncanplay = function (e) {
    };

    Audio.oncanplay = function (e) {
        if (typeof (_this.oncanplay) == "function") _this.oncanplay(e);
    };

    Audio.onplaying = function () {
        $(".total-time").text(formatTime(Audio.duration));
        $(".js-play-btn").removeClass("play-btn").addClass("pause-btn");
    };

    Audio.onprogress = function (e) {
        updateProgress();
    };

    Audio.onplay = Audio.onplaying;
    Audio.onended = function (e) {
        $(".js-play-btn").addClass("play-btn").removeClass("pause-btn");
        var $li = $(".play-list-ul li");
        switch (_this.loop) {
            case 0:
                Audio.pause();
                break;
            case 1:
                //单曲循环
                Audio.play(0);
                break;
            case 2:
                //列表播放
                if (_this.id < $li.last().data("id")) {
                    $(".play-list-ul li[data-id='" + _this.id + "']").next().click();
                }
                break;
            case 3:
                //列表循环
                if (_this.id < $li.last().data("id")) {
                    $(".play-list-ul li[data-id='" + _this.id + "']").next().click();
                } else if (_this.id == $li.last().data("id")) {
                    $li.first().click();
                }
                break;
            case 4:
                //随机播放
                var length = $li.length;
                var id = Math.floor(Math.random() * (length - 1));
                $li.removeClass("active").eq(id).click();
                break;
        }
        if (typeof (_this.onended) == "function") _this.onended(e);
    };

    Audio.onpause = function (e) {
        $(".js-play-btn").addClass("play-btn").removeClass("pause-btn");
    };

    Audio.ontimeupdate = function (e) {
        updateProgress();
    };

    Audio.ondurationchange = function (e) {
        $(".total-time").text(formatTime(e.target.duration));
    };


    function updateProgress() {
        var now = getCurrentTime();
        var total = Audio.duration * 1;
        var progress = now / total * 100;
        $(".controller-progress-finished").css("width", progress + "%");
        $(".current-time").text(formatTime(now));
    }

    function getCurrentTime() {
        if (Audio.ended) return 0;
        return Audio.currentTime;
    }

    this.play = function (offset) {
        Audio.currentTime = offset ? offset : 0;
        return Audio.play();
    };

    this.pause = function () {
        return Audio.pause();
    };

    this.stop = function () {
        Audio.currentTime = 0;
        return Audio.pause();
    };

    this.getCurrentTime = getCurrentTime;

    this.audio = Audio;
    this.context = AudioContext;
    this.updateProgress = updateProgress;
    this.source = source;
    this.gain = gain;
}

var App = new Player();
var App2 = new Lyric();

function Lyric() {
    var _this = this;

    this.offset = 0;
    this.word = true;
    this.frequence = 25;

    function init(lyric) {
        if (!lyric) $(".lyric-container").html("<h3>欢迎使用网页音乐播放器</h3>");
        else $(".lyric-container").html("<h3>载入歌词中……</h3>");
        $.get(lyric, {}, function (lyric) {
            $(".lyric-container").html("");

            var reg1 = /\[(\d+):(\d+)\.(\d+)]/ig;
            var reg2 = /<(\d+),(\d+)>([^<]+)/ig;
            _this.lyric_set = lyric.match(/(\[(\d+):(\d+)\.(\d+)]+((<\d+,\d+>)?[\S]* *)+)+/g);
            _this.lyric_set.forEach(function (v, i) {
                var start = reg1.exec(v) || reg1.exec(v);
                if (!start || !start[3]) return true;
                start = start[1] * 60 * 1000 + start[2] * 1000 + start[3] * 10 + 1;
                var row;
                var row_html = "<div class='lyric-row' data-start='" + start + "'><div class='lyric-wrapper'>";
                var caption = "";
                if (v.match(reg2))
                    while (row = reg2.exec(v)) {
                        caption += "<span data-start='" + row[1] + "' data-end='" + row[2] + "'>" + row[3] + "</span>";
                    }
                row_html += "<div class='lyric-before'>" + caption + "</div><div class='lyric-finished'>" + caption + "</div>";
                row_html += "</div></div>";
                $(row_html).appendTo(".lyric-container");
            });
            reset();
            this.timer = setInterval(function () {
                var current = Math.floor(App.getCurrentTime() * 1000) + _this.offset;
                $(".lyric-row").each(function (i, v) {
                    var $next = $(this).next(".lyric-row");
                    if (Math.floor($(this).attr("data-start")) <= current && ($next.length == 0 || Math.floor($next.attr("data-start")) > current)) {
                        //if (!$(this).prev().is(".lyric-row-finished"))$(this).prev().removeClass("lyric-current").addClass("lyric-row-finished").find(".lyric-finished").width("100%");
                        if (!$(this).is(".lyric-current")/* && Math.floor($(this).find("span").last().attr("data-end")) > current*/) {
                            $(this).addClass("lyric-current");
                            _this.centerLyric();
                        }
                        if (Math.floor($(this).find("span").last().attr("data-end")) < current) {
                            $(this)/*.addClass("lyric-row-finished").removeClass("lyric-current")*/.find(".lyric-finished").width("100%");
                        } else if (_this.word) {
                            var $cur = $(this);
                            $cur.find(".lyric-finished>span").each(function () {
                                if (Math.floor($(this).attr("data-start")) < current && Math.floor($(this).attr("data-end")) >= current) {
                                    var left = $(this).offset().left - $(this).parent().offset().left;
                                    var progress = (current - Math.floor($(this).attr("data-start"))) / (Math.floor($(this).attr("data-end")) - Math.floor($(this).attr("data-start")));
                                    var width = left + progress * $(this).width();
                                    if (width <= $(this).parent().parent().width()) {
                                        $(this).parent().width(width);
                                    }
                                }
                            });
                        } else {
                            $(this).find(".lyric-finished").width("100%");
                        }
                    } else if (Math.floor($(this).attr("data-start")) < current) {
                        $(this).addClass("lyric-row-finished").removeClass("lyric-current").find(".lyric-finished").width("0");
                    } else {
                        $(this).removeClass("lyric-row-finished lyric-current").find(".lyric-finished").width(0);
                    }
                });
            }, _this.frequence);
        });
    }

    function reset() {
        $(".lyric-row-finished,.lyric-current").removeClass("lyric-row-finished lyric-current").find(".lyric-finished").width(0);
        _this.centerLyric();
    }

    function centerLyric() {
        var $cur = $(".lyric-current").last();
        if (!$cur.length) {
            $cur = $(".lyric-row-finished").last();
            if (!$cur.length) $cur = $(".lyric-row").first();
            if (!$cur.length)return;
        }
        var top = $cur.position().top;
        top = top - $(".lyric").height() / 2 + $cur.height() / 2;
        $(".lyric-container").css("top", -top + "px");
    }

    this.init = init;
    this.reset = reset;
    this.centerLyric = centerLyric;
}


$(".js-play-btn").click(function () {
    if (App.audio.currentSrc)
        if ($(this).is(".play-btn")) {
            App.play(App.getCurrentTime());
        } else {
            App.pause();
        }
});
$(".prev-btn").click(function (e) {
    var $li = $("#play-list").find("li");
    if ($li.first().is(".active")) {
        $li.first().removeClass("active");
        $li.last().click();
    } else {
        $li.filter(".active").prev().click();
    }
});
$(".next-btn").click(function (e) {
    var $li = $("#play-list").find("li");
    if ($li.last().is(".active")) {
        $li.last().removeClass("active");
        $li.first().click();
    } else {
        $li.filter(".active").next().click();
    }
});

$(".play-list-btn").click(function (e) {
    var $list = $("#play-list");
    if ($list.is(":visible"))
        $(this).removeClass("active");
    else
        $(this).addClass("active");
    $list.slideToggle(200);
});

$("#play-list").find("li").click(function () {
    if (!$(this).is(".active")) {
        $("#play-list").find("li.active").removeClass("active");
        $(this).addClass("active");
        App.id = $(this).data("id");
        App.audio.src = $(this).data("music-src");
        App.oncanplay = function () {
            App.audio.play(0);
        };
        App2.init($(this).data("music-lrc"));
    }
});

$(".play-mode").click(function () {
    App.loop = (App.loop + 1) % 5;
    switch (App.loop) {
        case 0:
            //单曲播放
            $(this).find("span").prop("class", "mode-play-single");
            break;
        case 1:
            //单曲循环
            $(this).find("span").prop("class", "mode-loop-single");
            break;
        case 2:
            //列表播放
            $(this).find("span").prop("class", "mode-play-list");
            break;
        case 3:
            //列表循环
            $(this).find("span").prop("class", "mode-loop-list");
            break;
        case 4:
            //随机播放
            $(this).find("span").prop("class", "mode-random");
            break;
    }
});

$(".controller-progress").mousedown(function (e) {
    var scrollX = document.documentElement.scrollLeft || document.body.scrollLeft;
    var x = e.pageX || e.clientX + scrollX;
    var width = x - $(this).offset().left;
    $(".controller-progress-finished").css("width", width);
    var progress = width / $(this).width() * App.audio.duration;
    App2.reset();
    App.play(progress);
});

App.onended = function (e) {
    App.updateProgress();
    App2.reset();
};

$(window).resize(function () {
    App2.centerLyric();
});