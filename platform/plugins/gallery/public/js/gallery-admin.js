(() => {
    function t(e) {
        return (
            (t =
                "function" == typeof Symbol && "symbol" == typeof Symbol.iterator
                    ? function (t) {
                        return typeof t;
                    }
                    : function (t) {
                        return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t;
                    }),
                t(e)
        );
    }
    function e(t, e) {
        for (var n = 0; n < e.length; n++) {
            var r = e[n];
            (r.enumerable = r.enumerable || !1), (r.configurable = !0), "value" in r && (r.writable = !0), Object.defineProperty(t, o(r.key), r);
        }
    }
    function o(e) {
        var o = (function (e, o) {
            if ("object" != t(e) || !e) return e;
            var n = e[Symbol.toPrimitive];
            if (void 0 !== n) {
                var r = n.call(e, o || "default");
                if ("object" != t(r)) return r;
                throw new TypeError("@@toPrimitive must return a primitive value.");
            }
            return ("string" === o ? String : Number)(e);
        })(e, "string");
        return "symbol" == t(o) ? o : o + "";
    }
    var n = (function () {
        return (
            (t = function t() {
                !(function (t, e) {
                    if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function");
                })(this, t);
            }),
            (o = [
                {
                    key: "init",
                    value: function (t) {
                        var e = $(document).find("#".concat(t, "_wrapper"));
                        $.each(e.find("tbody"), function (t, e) {
                            Sortable.create(e, {
                                group: e + "_" + t,
                                sort: !0,
                                delay: 0,
                                disabled: !1,
                                store: null,
                                animation: 150,
                                handle: "tr",
                                ghostClass: "sortable-ghost",
                                chosenClass: "sortable-chosen",
                                dataIdAttr: "data-id",
                                forceFallback: !1,
                                fallbackClass: "sortable-fallback",
                                fallbackOnBody: !1,
                                scroll: !0,
                                scrollSensitivity: 30,
                                scrollSpeed: 10,
                                onEnd: function () {
                                    var t = $(e).closest(".card");
                                    t.find(".btn-save-sort-order").addClass("sort-button-active").show(),
                                        $.each(t.find("tbody tr"), function (t, e) {
                                            $(e)
                                                .find(".order-column")
                                                .text(t + 1);
                                        });
                                },
                            });
                        });
                        var o = e.closest(".card").find(".btn-save-sort-order");
                        o.off("click").on("click", function (t) {
                            t.preventDefault();
                            var e = $(t.currentTarget),
                                n = [];
                            $.each(e.closest(".card").find("tbody tr"), function (t, e) {
                                n.push(parseInt($(e).find("td:first-child").text())),
                                    $(e)
                                        .find(".order-column")
                                        .text(t + 1);
                            }),
                                Botble.showButtonLoading(e),
                                $httpClient
                                    .make()
                                    .post(o.data("url"), { items: n })
                                    .then(function (t) {
                                        var e = t.data;
                                        Botble.showSuccess(e.message);
                                    })
                                    .finally(function () {
                                        Botble.hideButtonLoading(e), e.hide();
                                    });
                        });
                    },
                },
            ]) && e(t.prototype, o),
            n && e(t, n),
                Object.defineProperty(t, "prototype", { writable: !1 }),
                t
        );
        var t, o, n;
    })();
    $(function () {
        document.addEventListener("core-table-init-completed", function (t) {
            new n().init(t.detail.table.prop("id"));
        }),
            $(document)
                .on("show.bs.modal", "#gallery-item-modal", function (t) {
                    var e = $(t.currentTarget),
                        o = $(t.relatedTarget).prop("href");
                    $httpClient
                        .make()
                        .withLoading(e.find(".modal-content"))
                        .get(o)
                        .then(function (t) {
                            var o = t.data;
                            e.find(".modal-header .modal-title").text(o.data.title), e.find(".modal-body").html(o.data.content), Botble.initMediaIntegrate(), Botble.initResources();
                        });
                })
                .on("click", '#gallery-item-modal button[type="submit"]', function (t) {
                    t.preventDefault();
                    var e = $(t.currentTarget),
                        o = e.closest(".modal"),
                        n = o.find("form");
                    $httpClient
                        .make()
                        .withLoading(n)
                        .withButtonLoading(e)
                        .post(n.prop("action"), n.serialize())
                        .then(function (t) {
                            var e = t.data;
                            Botble.showSuccess(e.message), o.modal("hide"), $("#botble-gallery-tables-gallery-item-table").DataTable().draw();
                        });
                });
    });
})();
