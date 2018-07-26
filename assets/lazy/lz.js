(function (a) {
    a.fn.lz = function (h, j) {
        var e = a(window),
                b = h || 0,
                i = this,
                g;
        this.one("lz", function () {
            var k = this.getAttribute("data-lz"),l=$(this);
            if (k) {
                $.get(k,function(r){
                    l.replaceWith(r);
                });
                if (typeof j === "function") {
                    j.call(this)
                }
            }
        });
        function c() {
            var k = i.filter(function () {
                var m = a(this);
                if (m.is(":hidden")) {
                    return
                }
                var l = e.scrollTop(),
                        o = l + e.height(),
                        p = m.offset().top,
                        n = p + m.height();
                return n >= l - b && p <= o + b
            });
            g = k.trigger("lz");
            i = i.not(g);
        }
        e.on("scroll.lz resize.lz lookup.lz", c);
        c();
        return this;
    }
})(window.jQuery);