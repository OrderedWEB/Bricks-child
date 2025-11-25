/**
 * Minified by jsDelivr using Terser v5.39.0.
 * Original file: /npm/@vivliostyle/core@2.36.2/lib/vivliostyle.js
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
"use strict";
var Mg = Object.create,
  Oo = Object.defineProperty,
  _g = Object.getOwnPropertyDescriptor,
  Ug = Object.getOwnPropertyNames,
  Hg = Object.getPrototypeOf,
  zg = Object.prototype.hasOwnProperty,
  Gg = (e, t, i) =>
    t in e
      ? Oo(e, t, { enumerable: !0, configurable: !0, writable: !0, value: i })
      : (e[t] = i),
  Wg = (e, t) => () => (t || e((t = { exports: {} }).exports, t), t.exports),
  $g = (e, t) => {
    for (var i in t) Oo(e, i, { get: t[i], enumerable: !0 });
  },
  Dp = (e, t, i, n) => {
    if ((t && "object" == typeof t) || "function" == typeof t)
      for (let r of Ug(t))
        !zg.call(e, r) &&
          r !== i &&
          Oo(e, r, {
            get: () => t[r],
            enumerable: !(n = _g(t, r)) || n.enumerable,
          });
    return e;
  },
  Xg = (e, t, i) => (
    (i = null != e ? Mg(Hg(e)) : {}),
    Dp(
      !t && e && e.__esModule
        ? i
        : Oo(i, "default", { value: e, enumerable: !0 }),
      e
    )
  ),
  jg = (e) => Dp(Oo({}, "__esModule", { value: !0 }), e),
  p = (e, t, i) => Gg(e, "symbol" != typeof t ? t + "" : t, i),
  _h = Wg((e, t) => {
    var i = -1;
    function n(e, t, f, g, m) {
      if (e === t) return e ? [[0, e]] : [];
      if (null != f) {
        var w = (function (e, t, i) {
          var n = "number" == typeof i ? { index: i, length: 0 } : i.oldRange,
            r = "number" == typeof i ? null : i.newRange,
            s = e.length,
            o = t.length;
          if (0 === n.length && (null === r || 0 === r.length)) {
            var a = n.index,
              l = e.slice(0, a),
              h = e.slice(a),
              u = r ? r.index : null,
              c = a + o - s;
            if (!((null !== u && u !== c) || c < 0 || c > o)) {
              var d = t.slice(0, c);
              if ((g = t.slice(c)) === h) {
                var p = Math.min(a, c);
                if ((w = l.slice(0, p)) === (y = d.slice(0, p)))
                  return b(w, l.slice(p), d.slice(p), h);
              }
            }
            if (null === u || u === a) {
              var f = a,
                g = ((d = t.slice(0, f)), t.slice(f));
              if (d === l) {
                var m = Math.min(s - f, o - f);
                if ((v = h.slice(h.length - m)) === (x = g.slice(g.length - m)))
                  return b(
                    l,
                    h.slice(0, h.length - m),
                    g.slice(0, g.length - m),
                    v
                  );
              }
            }
          }
          if (n.length > 0 && r && 0 === r.length) {
            var w = e.slice(0, n.index),
              v = e.slice(n.index + n.length);
            if (!(o < (p = w.length) + (m = v.length))) {
              var y = t.slice(0, p),
                x = t.slice(o - m);
              if (w === y && v === x)
                return b(w, e.slice(p, s - m), t.slice(p, o - m), v);
            }
          }
          return null;
        })(e, t, f);
        if (w) return w;
      }
      var v = s(e, t),
        y = e.substring(0, v);
      v = a((e = e.substring(v)), (t = t.substring(v)));
      var x = e.substring(e.length - v),
        S = (function (e, t) {
          var o;
          if (!e) return [[1, t]];
          if (!t) return [[i, e]];
          var l = e.length > t.length ? e : t,
            h = e.length > t.length ? t : e,
            u = l.indexOf(h);
          if (-1 !== u)
            return (
              (o = [
                [1, l.substring(0, u)],
                [0, h],
                [1, l.substring(u + h.length)],
              ]),
              e.length > t.length && (o[0][0] = o[2][0] = i),
              o
            );
          if (1 === h.length)
            return [
              [i, e],
              [1, t],
            ];
          var c = (function (e, t) {
            var i = e.length > t.length ? e : t,
              n = e.length > t.length ? t : e;
            if (i.length < 4 || 2 * n.length < i.length) return null;
            function r(e, t, i) {
              for (
                var n,
                  r,
                  o,
                  l,
                  h = e.substring(i, i + Math.floor(e.length / 4)),
                  u = -1,
                  c = "";
                -1 !== (u = t.indexOf(h, u + 1));

              ) {
                var d = s(e.substring(i), t.substring(u)),
                  p = a(e.substring(0, i), t.substring(0, u));
                c.length < p + d &&
                  ((c = t.substring(u - p, u) + t.substring(u, u + d)),
                  (n = e.substring(0, i - p)),
                  (r = e.substring(i + d)),
                  (o = t.substring(0, u - p)),
                  (l = t.substring(u + d)));
              }
              return 2 * c.length >= e.length ? [n, r, o, l, c] : null;
            }
            var o,
              l,
              h,
              u,
              c,
              d = r(i, n, Math.ceil(i.length / 4)),
              p = r(i, n, Math.ceil(i.length / 2));
            if (!d && !p) return null;
            (o = p ? (d && d[4].length > p[4].length ? d : p) : d),
              e.length > t.length
                ? ((l = o[0]), (h = o[1]), (u = o[2]), (c = o[3]))
                : ((u = o[0]), (c = o[1]), (l = o[2]), (h = o[3]));
            var f = o[4];
            return [l, h, u, c, f];
          })(e, t);
          if (c) {
            var d = c[0],
              p = c[1],
              f = c[2],
              g = c[3],
              m = c[4],
              w = n(d, f),
              b = n(p, g);
            return w.concat([[0, m]], b);
          }
          return (function (e, t) {
            for (
              var n = e.length,
                s = t.length,
                o = Math.ceil((n + s) / 2),
                a = o,
                l = 2 * o,
                h = new Array(l),
                u = new Array(l),
                c = 0;
              c < l;
              c++
            )
              (h[c] = -1), (u[c] = -1);
            (h[a + 1] = 0), (u[a + 1] = 0);
            for (
              var d = n - s, p = d % 2 != 0, f = 0, g = 0, m = 0, w = 0, b = 0;
              b < o;
              b++
            ) {
              for (var v = -b + f; v <= b - g; v += 2) {
                for (
                  var y = a + v,
                    x =
                      (P =
                        v === -b || (v !== b && h[y - 1] < h[y + 1])
                          ? h[y + 1]
                          : h[y - 1] + 1) - v;
                  P < n && x < s && e.charAt(P) === t.charAt(x);

                )
                  P++, x++;
                if (((h[y] = P), P > n)) g += 2;
                else if (x > s) f += 2;
                else if (p) {
                  if ((E = a + d - v) >= 0 && E < l && -1 !== u[E])
                    if (P >= (C = n - u[E])) return r(e, t, P, x);
                }
              }
              for (var S = -b + m; S <= b - w; S += 2) {
                for (
                  var C,
                    E = a + S,
                    k =
                      (C =
                        S === -b || (S !== b && u[E - 1] < u[E + 1])
                          ? u[E + 1]
                          : u[E - 1] + 1) - S;
                  C < n && k < s && e.charAt(n - C - 1) === t.charAt(s - k - 1);

                )
                  C++, k++;
                if (((u[E] = C), C > n)) w += 2;
                else if (k > s) m += 2;
                else if (!p) {
                  if ((y = a + d - S) >= 0 && y < l && -1 !== h[y]) {
                    var P;
                    x = a + (P = h[y]) - y;
                    if (P >= (C = n - C)) return r(e, t, P, x);
                  }
                }
              }
            }
            return [
              [i, e],
              [1, t],
            ];
          })(e, t);
        })(
          (e = e.substring(0, e.length - v)),
          (t = t.substring(0, t.length - v))
        );
      return (
        y && S.unshift([0, y]),
        x && S.push([0, x]),
        p(S, m),
        g &&
          (function (e) {
            for (
              var t = !1,
                n = [],
                r = 0,
                s = null,
                f = 0,
                g = 0,
                m = 0,
                w = 0,
                b = 0;
              f < e.length;

            )
              0 == e[f][0]
                ? ((n[r++] = f),
                  (g = w),
                  (m = b),
                  (w = 0),
                  (b = 0),
                  (s = e[f][1]))
                : (1 == e[f][0] ? (w += e[f][1].length) : (b += e[f][1].length),
                  s &&
                    s.length <= Math.max(g, m) &&
                    s.length <= Math.max(w, b) &&
                    (e.splice(n[r - 1], 0, [i, s]),
                    (e[n[r - 1] + 1][0] = 1),
                    r--,
                    r--,
                    (f = r > 0 ? n[r - 1] : -1),
                    (g = 0),
                    (m = 0),
                    (w = 0),
                    (b = 0),
                    (s = null),
                    (t = !0))),
                f++;
            for (
              t && p(e),
                (function (e) {
                  function t(e, t) {
                    if (!e || !t) return 6;
                    var i = e.charAt(e.length - 1),
                      n = t.charAt(0),
                      r = i.match(l),
                      s = n.match(l),
                      o = r && i.match(h),
                      a = s && n.match(h),
                      p = o && i.match(u),
                      f = a && n.match(u),
                      g = p && e.match(c),
                      m = f && t.match(d);
                    return g || m
                      ? 5
                      : p || f
                      ? 4
                      : r && !o && a
                      ? 3
                      : o || a
                      ? 2
                      : r || s
                      ? 1
                      : 0;
                  }
                  for (var i = 1; i < e.length - 1; ) {
                    if (0 == e[i - 1][0] && 0 == e[i + 1][0]) {
                      var n = e[i - 1][1],
                        r = e[i][1],
                        s = e[i + 1][1],
                        o = a(n, r);
                      if (o) {
                        var p = r.substring(r.length - o);
                        (n = n.substring(0, n.length - o)),
                          (r = p + r.substring(0, r.length - o)),
                          (s = p + s);
                      }
                      for (
                        var f = n, g = r, m = s, w = t(n, r) + t(r, s);
                        r.charAt(0) === s.charAt(0);

                      ) {
                        (n += r.charAt(0)),
                          (r = r.substring(1) + s.charAt(0)),
                          (s = s.substring(1));
                        var b = t(n, r) + t(r, s);
                        b >= w && ((w = b), (f = n), (g = r), (m = s));
                      }
                      e[i - 1][1] != f &&
                        (f ? (e[i - 1][1] = f) : (e.splice(i - 1, 1), i--),
                        (e[i][1] = g),
                        m ? (e[i + 1][1] = m) : (e.splice(i + 1, 1), i--));
                    }
                    i++;
                  }
                })(e),
                f = 1;
              f < e.length;

            ) {
              if (e[f - 1][0] == i && 1 == e[f][0]) {
                var v = e[f - 1][1],
                  y = e[f][1],
                  x = o(v, y),
                  S = o(y, v);
                x >= S
                  ? (x >= v.length / 2 || x >= y.length / 2) &&
                    (e.splice(f, 0, [0, y.substring(0, x)]),
                    (e[f - 1][1] = v.substring(0, v.length - x)),
                    (e[f + 1][1] = y.substring(x)),
                    f++)
                  : (S >= v.length / 2 || S >= y.length / 2) &&
                    (e.splice(f, 0, [0, v.substring(0, S)]),
                    (e[f - 1][0] = 1),
                    (e[f - 1][1] = y.substring(0, y.length - S)),
                    (e[f + 1][0] = i),
                    (e[f + 1][1] = v.substring(S)),
                    f++),
                  f++;
              }
              f++;
            }
          })(S),
        S
      );
    }
    function r(e, t, i, r) {
      var s = e.substring(0, i),
        o = t.substring(0, r),
        a = e.substring(i),
        l = t.substring(r),
        h = n(s, o),
        u = n(a, l);
      return h.concat(u);
    }
    function s(e, t) {
      if (!e || !t || e.charAt(0) !== t.charAt(0)) return 0;
      for (var i = 0, n = Math.min(e.length, t.length), r = n, s = 0; i < r; )
        e.substring(s, r) == t.substring(s, r) ? (s = i = r) : (n = r),
          (r = Math.floor((n - i) / 2 + i));
      return f(e.charCodeAt(r - 1)) && r--, r;
    }
    function o(e, t) {
      var i = e.length,
        n = t.length;
      if (0 == i || 0 == n) return 0;
      i > n ? (e = e.substring(i - n)) : i < n && (t = t.substring(0, i));
      var r = Math.min(i, n);
      if (e == t) return r;
      for (var s = 0, o = 1; ; ) {
        var a = e.substring(r - o),
          l = t.indexOf(a);
        if (-1 == l) return s;
        (o += l),
          (0 == l || e.substring(r - o) == t.substring(0, o)) && ((s = o), o++);
      }
    }
    function a(e, t) {
      if (!e || !t || e.slice(-1) !== t.slice(-1)) return 0;
      for (var i = 0, n = Math.min(e.length, t.length), r = n, s = 0; i < r; )
        e.substring(e.length - r, e.length - s) ==
        t.substring(t.length - r, t.length - s)
          ? (s = i = r)
          : (n = r),
          (r = Math.floor((n - i) / 2 + i));
      return g(e.charCodeAt(e.length - r)) && r--, r;
    }
    var l = /[^a-zA-Z0-9]/,
      h = /\s/,
      u = /[\r\n]/,
      c = /\n\r?\n$/,
      d = /^\r?\n\r?\n/;
    function p(e, t) {
      e.push([0, ""]);
      for (var n, r = 0, o = 0, l = 0, h = "", u = ""; r < e.length; )
        if (r < e.length - 1 && !e[r][1]) e.splice(r, 1);
        else
          switch (e[r][0]) {
            case 1:
              l++, (u += e[r][1]), r++;
              break;
            case i:
              o++, (h += e[r][1]), r++;
              break;
            case 0:
              var c = r - l - o - 1;
              if (t) {
                if (c >= 0 && w(e[c][1])) {
                  var d = e[c][1].slice(-1);
                  if (
                    ((e[c][1] = e[c][1].slice(0, -1)),
                    (h = d + h),
                    (u = d + u),
                    !e[c][1])
                  ) {
                    e.splice(c, 1), r--;
                    var f = c - 1;
                    e[f] && 1 === e[f][0] && (l++, (u = e[f][1] + u), f--),
                      e[f] && e[f][0] === i && (o++, (h = e[f][1] + h), f--),
                      (c = f);
                  }
                }
                if (m(e[r][1])) {
                  d = e[r][1].charAt(0);
                  (e[r][1] = e[r][1].slice(1)), (h += d), (u += d);
                }
              }
              if (r < e.length - 1 && !e[r][1]) {
                e.splice(r, 1);
                break;
              }
              if (h.length > 0 || u.length > 0) {
                h.length > 0 &&
                  u.length > 0 &&
                  (0 !== (n = s(u, h)) &&
                    (c >= 0
                      ? (e[c][1] += u.substring(0, n))
                      : (e.splice(0, 0, [0, u.substring(0, n)]), r++),
                    (u = u.substring(n)),
                    (h = h.substring(n))),
                  0 !== (n = a(u, h)) &&
                    ((e[r][1] = u.substring(u.length - n) + e[r][1]),
                    (u = u.substring(0, u.length - n)),
                    (h = h.substring(0, h.length - n))));
                var g = l + o;
                0 === h.length && 0 === u.length
                  ? (e.splice(r - g, g), (r -= g))
                  : 0 === h.length
                  ? (e.splice(r - g, g, [1, u]), (r = r - g + 1))
                  : 0 === u.length
                  ? (e.splice(r - g, g, [i, h]), (r = r - g + 1))
                  : (e.splice(r - g, g, [i, h], [1, u]), (r = r - g + 2));
              }
              0 !== r && 0 === e[r - 1][0]
                ? ((e[r - 1][1] += e[r][1]), e.splice(r, 1))
                : r++,
                (l = 0),
                (o = 0),
                (h = ""),
                (u = "");
          }
      "" === e[e.length - 1][1] && e.pop();
      var b = !1;
      for (r = 1; r < e.length - 1; )
        0 === e[r - 1][0] &&
          0 === e[r + 1][0] &&
          (e[r][1].substring(e[r][1].length - e[r - 1][1].length) ===
          e[r - 1][1]
            ? ((e[r][1] =
                e[r - 1][1] +
                e[r][1].substring(0, e[r][1].length - e[r - 1][1].length)),
              (e[r + 1][1] = e[r - 1][1] + e[r + 1][1]),
              e.splice(r - 1, 1),
              (b = !0))
            : e[r][1].substring(0, e[r + 1][1].length) == e[r + 1][1] &&
              ((e[r - 1][1] += e[r + 1][1]),
              (e[r][1] = e[r][1].substring(e[r + 1][1].length) + e[r + 1][1]),
              e.splice(r + 1, 1),
              (b = !0))),
          r++;
      b && p(e, t);
    }
    function f(e) {
      return e >= 55296 && e <= 56319;
    }
    function g(e) {
      return e >= 56320 && e <= 57343;
    }
    function m(e) {
      return g(e.charCodeAt(0));
    }
    function w(e) {
      return f(e.charCodeAt(e.length - 1));
    }
    function b(e, t, n, r) {
      return w(e) || m(r)
        ? null
        : (function (e) {
            for (var t = [], i = 0; i < e.length; i++)
              e[i][1].length > 0 && t.push(e[i]);
            return t;
          })([
            [0, e],
            [i, t],
            [1, n],
            [0, r],
          ]);
    }
    function v(e, t, i, r) {
      return n(e, t, i, r, !0);
    }
    (v.INSERT = 1), (v.DELETE = i), (v.EQUAL = 0), (t.exports = v);
  }),
  zb = {};
$g(zb, {
  CoreViewer: () => Zi,
  HOOKS: () => ft,
  Navigation: () => Ig,
  PageProgression: () => as,
  PageSide: () => Do,
  PageViewMode: () => Ub,
  Profiler: () => or,
  ReadyState: () => El,
  UserAgentBaseCss: () => Ec,
  UserAgentPageCss: () => yc,
  UserAgentTocCss: () => Sc,
  UserAgentXml: () => xc,
  ValidationTxt: () => bc,
  VivliostylePolyfillCss: () => Nc,
  VivliostyleViewportCss: () => Cc,
  VivliostyleViewportScreenCss: () => mc,
  ZoomType: () => _b,
  getHooksForName: () => Ge,
  isDebug: () => rs,
  pageProgressionOf: () => yl,
  plugin: () => Yg,
  printHTML: () => Hb,
  profile: () => Qg,
  profiler: () => Ye,
  registerHook: () => Ue,
  removeHook: () => Mp,
  setDebug: () => xl,
}),
  (module.exports = jg(zb));
var rs = !1;
function xl(e) {
  rs = e;
}
var as = ((e) => ((e.LTR = "ltr"), (e.RTL = "rtl"), e))(as || {});
function yl(e) {
  switch (e) {
    case "ltr":
      return "ltr";
    case "rtl":
      return "rtl";
    default:
      throw new Error(`unknown PageProgression: ${e}`);
  }
}
var Do = ((e) => ((e.LEFT = "left"), (e.RIGHT = "right"), e))(Do || {}),
  El = ((e) => (
    (e.LOADING = "loading"),
    (e.INTERACTIVE = "interactive"),
    (e.COMPLETE = "complete"),
    e
  ))(El || {}),
  Nl = ((e) => (
    (e[(e.DEBUG = 1)] = "DEBUG"),
    (e[(e.INFO = 2)] = "INFO"),
    (e[(e.WARN = 3)] = "WARN"),
    (e[(e.ERROR = 4)] = "ERROR"),
    e
  ))(Nl || {}),
  Sl = class {
    constructor(e) {
      (this.opt_console = e), p(this, "listeners", {});
    }
    consoleDebug(e) {
      this.opt_console
        ? this.opt_console.debug
          ? this.opt_console.debug(...e)
          : this.opt_console.log(...e)
        : console.debug(...e);
    }
    consoleInfo(e) {
      this.opt_console
        ? this.opt_console.info
          ? this.opt_console.info(...e)
          : this.opt_console.log(...e)
        : console.info(...e);
    }
    consoleWarn(e) {
      this.opt_console
        ? this.opt_console.warn
          ? this.opt_console.warn(...e)
          : this.opt_console.log(...e)
        : console.warn(...e);
    }
    consoleError(e) {
      this.opt_console
        ? this.opt_console.error
          ? this.opt_console.error(...e)
          : this.opt_console.log(...e)
        : console.error(...e);
    }
    triggerListeners(e, t) {
      let i = this.listeners[e];
      i && i(t);
    }
    addListener(e, t) {
      this.listeners[e] = t;
    }
    debug(...e) {
      let t = tr(arguments);
      this.consoleDebug(nr(t)), this.triggerListeners(1, t);
    }
    info(...e) {
      let t = tr(arguments);
      this.consoleInfo(nr(t)), this.triggerListeners(2, t);
    }
    warn(...e) {
      let t = tr(arguments);
      this.consoleWarn(nr(t)), this.triggerListeners(3, t);
    }
    error(...e) {
      let t = tr(arguments);
      this.consoleError(nr(t)), this.triggerListeners(4, t);
    }
  };
function tr(e) {
  let t = Array.from(e),
    i = null;
  return t[0] instanceof Error && (i = t.shift()), { error: i, messages: t };
}
function nr(e) {
  let t = e.error,
    i = t && (t.frameTrace || t.stack),
    n = [].concat(e.messages);
  return (
    t &&
      (n.length > 0 && (n = n.concat(["\n"])),
      (n = n.concat([t.toString()])),
      i && (n = n.concat(["\n"]).concat(i))),
    n
  );
}
var V = new Sl(),
  ft = ((e) => (
    (e.SIMPLE_PROPERTY = "SIMPLE_PROPERTY"),
    (e.PREPROCESS_SINGLE_DOCUMENT = "PREPROCESS_SINGLE_DOCUMENT"),
    (e.PREPROCESS_TEXT_CONTENT = "PREPROCESS_TEXT_CONTENT"),
    (e.PREPROCESS_ELEMENT_STYLE = "PREPROCESS_ELEMENT_STYLE"),
    (e.POLYFILLED_INHERITED_PROPS = "POLYFILLED_INHERITED_PROPS"),
    (e.CONFIGURATION = "CONFIGURATION"),
    (e.RESOLVE_TEXT_NODE_BREAKER = "RESOLVE_TEXT_NODE_BREAKER"),
    (e.RESOLVE_FORMATTING_CONTEXT = "RESOLVE_FORMATTING_CONTEXT"),
    (e.RESOLVE_LAYOUT_PROCESSOR = "RESOLVE_LAYOUT_PROCESSOR"),
    (e.POST_LAYOUT_BLOCK = "POST_LAYOUT_BLOCK"),
    e
  ))(ft || {}),
  sr = {};
function Ue(e, t, i) {
  if (ft[e]) {
    let n = sr[e];
    n || (n = sr[e] = []), i ? n.unshift(t) : n.push(t);
  } else V.warn(new Error(`Skipping unknown plugin hook '${e}'.`));
}
function Mp(e, t) {
  if (ft[e]) {
    let i = sr[e];
    if (i) {
      let e = i.indexOf(t);
      e >= 0 && i.splice(e, 1);
    }
  } else V.warn(new Error(`Ignoring unknown plugin hook '${e}'.`));
}
function Ge(e) {
  return sr[e] || [];
}
var Yg = { registerHook: Ue, removeHook: Mp },
  or = class {
    constructor(e) {
      (this.performanceInstance = e),
        p(this, "timestamps", {}),
        p(this, "registerTiming"),
        p(this, "registerStartTiming"),
        p(this, "registerEndTiming"),
        (this.registerTiming = Ws),
        (this.registerStartTiming = this.registerStartTiming = Ws),
        (this.registerEndTiming = this.registerEndTiming = Ws);
    }
    forceRegisterStartTiming(e, t) {
      vl.call(this, e, "start", t);
    }
    forceRegisterEndTiming(e, t) {
      vl.call(this, e, "end", t);
    }
    printTimings() {
      let e = this.timestamps,
        t = "";
      Object.keys(e).forEach((i) => {
        let n = e[i],
          r = n.length;
        for (let e = 0; e < r; e++) {
          let s = n[e];
          (t += i),
            r > 1 && (t += `(${e})`),
            (t += ` => start: ${s.start}, end: ${s.end}, duration: ${
              s.end - s.start
            }\n`);
        }
      }),
        V.info(t);
    }
    disable() {
      (this.registerTiming = Ws),
        (this.registerStartTiming = this.registerStartTiming = Ws),
        (this.registerEndTiming = this.registerEndTiming = Ws);
    }
    enable() {
      (this.registerTiming = vl),
        (this.registerStartTiming = this.registerStartTiming = _p),
        (this.registerEndTiming = this.registerEndTiming = qg);
    }
    isEnabled() {
      return this.registerStartTiming === _p;
    }
  };
function Ws() {}
function vl(e, t, i) {
  i || (i = this.performanceInstance.now());
  let n,
    r = this.timestamps[e];
  r || (r = this.timestamps[e] = []);
  for (let e = r.length - 1; e >= 0 && ((n = r[e]), !n || n[t]); e--) n = null;
  n || ((n = {}), r.push(n)), (n[t] = i);
}
function _p(e, t) {
  this.registerTiming(e, "start", t);
}
function qg(e, t) {
  this.registerTiming(e, "end", t);
}
var Kg = { now: Date.now },
  Zg = window && window.performance,
  Ye = new or(Zg || Kg);
Ye.forceRegisterStartTiming("load_vivliostyle");
var Qg = {
    profiler: {
      registerStartTiming: Ye.registerStartTiming,
      registerEndTiming: Ye.registerEndTiming,
      printTimings: Ye.printTimings,
      disable: Ye.disable,
      enable: Ye.enable,
    },
  },
  ls =
    /^[\s\p{Zs}\p{P}\p{Mn}]*[\p{L}\p{N}]\p{Mn}*(?:[\s\p{Zs}]*\p{P}\p{Mn}*)*/u,
  Ft = "data-adapt-eloff",
  Hp = {};
function Xs(e) {
  return JSON.parse(e);
}
function kt(e) {
  let t = e.match(/^([^#]*)/);
  return t ? t[1] : e;
}
function Jg(e) {
  let t = e.match(/^([^#?]*)/);
  return t ? t[1] : e;
}
var dn = window.location.href;
function wl(e) {
  dn = e;
}
var qt = window.location.href;
function Pl(e) {
  qt = e;
}
function J(e, t) {
  if (/^data:/i.test(t)) return e || t;
  if (!t || e.match(/^\w{2,}:/))
    return e.toLowerCase().match("^javascript:")
      ? "#"
      : (e.match(/^\w{2,}:\/\/[^\/]+$/) && (e = `${e}/`), e);
  let i;
  if ((t.match(/^\w{2,}:\/\/[^\/]+$/) && (t = `${t}/`), e.match(/^\/\//)))
    return (i = t.match(/^(\w{2,}:)\/\//)), i ? i[1] + e : e;
  if (e.match(/^\//))
    return (i = t.match(/^(\w{2,}:\/\/[^\/]+)\//)), i ? i[1] + e : e;
  if ((e.match(/^\.(\/|$)/) && (e = e.substr(2)), (t = Jg(t)), e.match(/^#/)))
    return t + e;
  let n = t.lastIndexOf("/");
  if (n < 0) return e;
  let r = t.substr(0, n + 1) + e,
    s = "";
  for (
    i = r.match(/^([^?#]*)([?#].*)$/), i && ((r = i[1]), (s = i[2]));
    (n = r.indexOf("/../")), !(n <= 0);

  ) {
    let e = r.lastIndexOf("/", n - 1);
    if (e <= 0) break;
    r = r.substr(0, e) + r.substr(n + 3);
  }
  return r.replace(/\/(\.\/)+/g, "/") + s;
}
function An(e) {
  let t;
  return (
    (t =
      /^(https?:)\/\/github\.com\/([^/]+\/[^/]+)\/(blob\/|tree\/|raw\/)?(.*)$/.exec(
        e
      ))
      ? (e = `${t[1]}//raw.githubusercontent.com/${t[2]}/${
          t[3] ? "" : "master/"
        }${t[4]}`)
      : (t =
          /^(https?:)\/\/www\.aozora\.gr\.jp\/(cards\/[^/]+\/files\/[^/.]+\.html)$/.exec(
            e
          ))
      ? (e = `${t[1]}//raw.githubusercontent.com/aozorabunko/aozorabunko/master/${t[2]}`)
      : (t =
          /^(https?:)\/\/gist\.github\.com\/([^/]+\/\w+)(\/|$)(raw(\/|$))?(.*)$/.exec(
            e
          ))
      ? (e = `${t[1]}//gist.githubusercontent.com/${t[2]}/raw/${t[6]}`)
      : (t =
          /^(https?:)\/\/gist\.github\.com\/([^/]+\/\w+)(?:#|%23)file-(.*)-(\w+)$/.exec(
            e
          ))
      ? (e = `${t[1]}//gist.githubusercontent.com/${t[2]}/raw/${t[3]}.${t[4]}`)
      : (t =
          /^(https?:)\/\/(?:[^/.]+\.)?jsbin\.com\/(?!(?:blog|help)\b)(\w+)((\/\d+)?).*$/.exec(
            e
          )) && (e = `${t[1]}//output.jsbin.com/${t[2]}${t[3]}/`),
    e
  );
}
function kl(e) {
  return null == e ? e : e.toString();
}
var ir = class {
    constructor() {
      p(this, "queue", [null]);
    }
    length() {
      return this.queue.length - 1;
    }
    add(e) {
      let t = this.queue.length;
      for (; t > 1; ) {
        let i = Math.floor(t / 2),
          n = this.queue[i];
        if (n.compare(e) > 0) return void (this.queue[t] = e);
        (this.queue[t] = n), (t = i);
      }
      this.queue[1] = e;
    }
    peek() {
      return this.queue[1];
    }
    remove() {
      let e = this.queue[1],
        t = this.queue.pop(),
        i = this.queue.length;
      if (i > 1) {
        let e = 1;
        for (;;) {
          let n = 2 * e;
          if (n >= i) break;
          if (this.queue[n].compare(t) > 0)
            n + 1 < i && this.queue[n + 1].compare(this.queue[n]) > 0 && n++;
          else {
            if (!(n + 1 < i && this.queue[n + 1].compare(t) > 0)) break;
            n++;
          }
          (this.queue[e] = this.queue[n]), (e = n);
        }
        this.queue[e] = t;
      }
      return e;
    }
  },
  Al = ["", "-webkit-", "-moz-"],
  $s = {};
function Tl(e, t) {
  return CSS.supports(e + t, "unset");
}
function em(e) {
  let t = $s[e];
  if (t || null === t) return t;
  switch (e) {
    case "behavior":
    case "template":
    case "ua-list-item-count":
    case "x-first-pseudo":
      return ($s[e] = null), null;
    case "text-combine-upright":
      if (Tl("-webkit-", "text-combine") && !Tl("", "text-combine-upright"))
        return ($s[e] = ["-webkit-text-combine"]), ["-webkit-text-combine"];
  }
  for (let i of Al) if (Tl(i, e)) return (t = [i + e]), ($s[e] = t), t;
  return (
    V.warn("Property not supported by the browser: ", e), ($s[e] = null), null
  );
}
function w(e, t, i) {
  let n = null == e ? void 0 : e.style;
  if (!n) return;
  if (t.startsWith("--")) return void n.setProperty(t, i || " ");
  let r = em(t);
  if (r)
    for (let e of r) {
      switch (e) {
        case "-webkit-text-combine":
          if ("all" === i) i = "horizontal";
          break;
        case "text-combine-upright":
          if ("all" === i) n.setProperty("text-indent", "0");
      }
      n.setProperty(e, i);
    }
}
function Bt(e, t, i) {
  try {
    let i = $s[t];
    return e.style.getPropertyValue(i ? i[0] : t);
  } catch (e) {}
  return i || "";
}
function _o(e) {
  let t = e.getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang");
  return (
    !t &&
      "http://www.w3.org/1999/xhtml" == e.namespaceURI &&
      (t = e.getAttribute("lang")),
    t
  );
}
var $e = class {
  constructor() {
    p(this, "list", []);
  }
  append(e) {
    return this.list.push(e), this;
  }
  clear() {
    this.list = [];
  }
  toString() {
    let e = this.list.join("");
    return (this.list = [e]), e;
  }
};
function zp(e) {
  return `\\${e.charCodeAt(0).toString(16)} `;
}
function Kt(e) {
  return e.replace(/[^-_a-zA-Z0-9\u0080-\uFFFF]/g, zp);
}
function cs(e) {
  return e.replace(/[\u0000-\u001F"\\]/g, zp);
}
function Ll(e) {
  return e.replace(/[\s+&?=#\u007F-\uFFFF]+/g, encodeURIComponent);
}
function Rl(e) {
  return !!e.match(
    /^[a-zA-Z\u009E\u009F\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u024F\u037B-\u037D\u0386\u0388-\u0482\u048A-\u0527]$/
  );
}
function tm(e, t) {
  return (
    (t = "string" == typeof t ? t : "\\u") +
    (65536 | e.charCodeAt(0)).toString(16).substr(1)
  );
}
function Il(e, t) {
  return e.replace(/[^-a-zA-Z0-9_]/g, function (e) {
    return tm(e, t);
  });
}
function pn(e) {
  return Il(e);
}
function nm(e, t) {
  return (
    (t = "string" == typeof t ? t : "\\u"),
    0 === e.indexOf(t)
      ? String.fromCharCode(parseInt(e.substring(t.length), 16))
      : e
  );
}
function Gp(e, t) {
  t = "string" == typeof t ? t : "\\u";
  let i = new RegExp(`${pn(t)}[0-9a-fA-F]{4}`, "g");
  return e.replace(i, function (e) {
    return nm(e, t);
  });
}
function gt(e, t) {
  let i = 0,
    n = e;
  for (;;) {
    if ((0 == i || t(i - 1), n == e || t(n), i == n)) return i;
    let r = (i + n) >> 1;
    t(r) ? (n = r) : (i = r + 1);
  }
}
function rr(e, t) {
  return e - t;
}
function Vl(e, t) {
  let i = {};
  for (let n of e) {
    let e = t(n);
    e && !i[e] && (i[e] = n);
  }
  return i;
}
function Wp(e) {
  let t = {};
  for (let i = 0; i < e.length; i++) t[e[i]] = !0;
  return t;
}
function ar(e, t) {
  let i = {};
  for (let n of e) {
    let e = t(n);
    e && (i[e] ? i[e].push(n) : (i[e] = [n]));
  }
  return i;
}
function $p(e, t) {
  let i = {};
  for (let n in e) i[n] = t(e[n], n);
  return i;
}
var kn = class {
    constructor() {
      p(this, "listeners", {});
    }
    dispatchEvent(e) {
      let t = this.listeners[e.type];
      if (t) {
        (e.target = this), (e.currentTarget = this);
        for (let i = 0; i < t.length; i++) t[i](e);
      }
    }
    addEventListener(e, t, i) {
      if (i) return;
      let n = this.listeners[e];
      n ? n.includes(t) || n.push(t) : (this.listeners[e] = [t]);
    }
    removeEventListener(e, t, i) {
      if (i) return;
      let n = this.listeners[e];
      if (n) {
        let e = n.indexOf(t);
        e >= 0 && n.splice(e, 1);
      }
    }
  },
  Zt = {
    audio: !0,
    canvas: !0,
    embed: !0,
    iframe: !0,
    img: !0,
    math: !0,
    object: !0,
    picture: !0,
    svg: !0,
    video: !0,
  };
function Yp(e) {
  if (1 == e.nodeType) {
    let t = e.getAttribute("id");
    if (t) return t;
  }
  return null;
}
function sm(e) {
  return `^${e}`;
}
function Xp(e) {
  return e.replace(/[\[\]\(\),=;^]/g, sm);
}
function om(e) {
  return e.substr(1);
}
function Fl(e) {
  return e && e.replace(/\^[\[\]\(\),=;^]/g, om);
}
function im(e) {
  let t = [];
  do {
    let i = e.match(/^(\^,|[^,])*/),
      n = Fl(i[0]);
    if (!(e = e.substr(i[0].length + 1)) && !t.length) return n;
    t.push(n);
  } while (e);
  return t;
}
function jp(e) {
  let t = {};
  for (; e; ) {
    let i = e.match(/^;([^;=]+)=(([^;]|\^;)*)/);
    if (!i) return t;
    (t[i[1]] = im(i[2])), (e = e.substr(i[0].length));
  }
  return t;
}
var lr = class {
    appendTo(e) {
      e.append("!");
    }
    applyTo(e) {
      return !1;
    }
  },
  cr = class {
    constructor(e, t, i) {
      (this.index = e), (this.id = t), (this.sideBias = i);
    }
    appendTo(e) {
      e.append("/"),
        e.append(this.index.toString()),
        (this.id || this.sideBias) &&
          (e.append("["),
          this.id && e.append(this.id),
          this.sideBias && (e.append(";s="), e.append(this.sideBias)),
          e.append("]"));
    }
    applyTo(e) {
      if (1 != e.node.nodeType) throw new Error("E_CFI_NOT_ELEMENT");
      let t,
        i = e.node,
        n = i.children,
        r = n.length,
        s = Math.floor(this.index / 2) - 1;
      if (s < 0 || 0 == r) (t = i.firstChild), (e.node = t || i);
      else {
        if (((t = n[Math.min(s, r - 1)]), 1 & this.index)) {
          let i = t.nextSibling;
          i && 1 != i.nodeType ? (t = i) : (e.after = !0);
        }
        e.node = t;
      }
      if (this.id && (e.after || this.id != Yp(e.node))) {
        let t = i.ownerDocument.getElementById(this.id);
        t ? (e.node = t) : V.warn("E_CFI_ID_MISMATCH:", this.id);
      }
      return (e.sideBias = this.sideBias), !0;
    }
  },
  ur = class {
    constructor(e, t, i, n) {
      (this.offset = e),
        (this.textBefore = t),
        (this.textAfter = i),
        (this.sideBias = n);
    }
    applyTo(e) {
      if (this.offset > 0 && !e.after) {
        let t = this.offset,
          i = e.node;
        for (;;) {
          let e = i.nodeType;
          if (1 == e) break;
          let n = i.nextSibling;
          if (3 <= e && e <= 5) {
            let e = i.textContent.length;
            if (t <= e) break;
            if (!n) {
              t = e;
              break;
            }
            t -= e;
          }
          if (!n) {
            t = 0;
            break;
          }
          i = n;
        }
        (e.node = i), (e.offset = t);
      }
      return (e.sideBias = this.sideBias), !0;
    }
    appendTo(e) {
      e.append(":"),
        e.append(this.offset.toString()),
        (this.textBefore || this.textAfter || this.sideBias) &&
          (e.append("["),
          (this.textBefore || this.textAfter) &&
            (this.textBefore && e.append(Xp(this.textBefore)),
            e.append(","),
            this.textAfter && e.append(Xp(this.textAfter))),
          this.sideBias && (e.append(";s="), e.append(this.sideBias)),
          e.append("]"));
    }
  },
  Uo = class e {
    constructor() {
      p(this, "steps", null);
    }
    fromString(e) {
      let t = e.match(/^#?epubcfi\((.*)\)$/);
      if (!t) throw new Error("E_CFI_NOT_CFI");
      let i = decodeURIComponent(t[1]),
        n = 0,
        r = [];
      for (;;) {
        let e;
        switch (i.charAt(n)) {
          case "/": {
            if (
              (n++,
              (t = i
                .substr(n)
                .match(/^(0|[1-9][0-9]*)(\[(.*?)(;([^\]]|\^\])*)?\])?/)),
              !t)
            )
              throw new Error("E_CFI_NUMBER_EXPECTED");
            n += t[0].length;
            let s = parseInt(t[1], 10),
              o = t[3];
            (e = jp(t[4])), r.push(new cr(s, o, kl(e.s)));
            break;
          }
          case ":": {
            if (
              (n++,
              (t = i
                .substr(n)
                .match(
                  /^(0|[1-9][0-9]*)(\[((([^\];,]|\^[\];,])*)(,(([^\];,]|\^[\];,])*))?)(;([^]]|\^\])*)?\])?/
                )),
              !t)
            )
              throw new Error("E_CFI_NUMBER_EXPECTED");
            n += t[0].length;
            let s = parseInt(t[1], 10),
              o = t[4];
            o && (o = Fl(o));
            let a = t[7];
            a && (a = Fl(a)), (e = jp(t[10])), r.push(new ur(s, o, a, kl(e.s)));
            break;
          }
          case "!":
            n++, r.push(new lr());
            break;
          case "~":
          case "@":
          case "":
            return void (this.steps = r);
          default:
            throw new Error("E_CFI_PARSE_ERROR");
        }
      }
    }
    navigate(t) {
      let i = {
        node: t.documentElement,
        offset: 0,
        after: !1,
        sideBias: null,
        ref: null,
      };
      for (let t = 0; t < this.steps.length; t++)
        if (!this.steps[t].applyTo(i)) {
          (i.ref = new e()), (i.ref.steps = this.steps.slice(t + 1));
          break;
        }
      return i;
    }
    trim(e, t) {
      return e
        .replace(/\s+/g, " ")
        .match(
          t ? /^[ -\uD7FF\uE000-\uFFFF]{0,8}/ : /[ -\uD7FF\uE000-\uFFFF]{0,8}$/
        )[0]
        .replace(/^\s/, "")
        .replace(/\s$/, "");
    }
    prependPathFromNode(e, t, i, n) {
      let r = [],
        s = e.parentNode,
        o = "",
        a = "";
      for (; e; ) {
        switch (e.nodeType) {
          case 3:
          case 4:
          case 5: {
            let n = e.textContent,
              r = n.length;
            i
              ? ((t += r), o || (o = n))
              : (t > r && (t = r),
                (i = !0),
                (o = n.substr(0, t)),
                (a = n.substr(t))),
              (e = e.previousSibling);
            continue;
          }
          case 8:
            e = e.previousSibling;
            continue;
        }
        break;
      }
      for (
        (t > 0 || o || a) &&
        ((o = this.trim(o, !1)),
        (a = this.trim(a, !0)),
        r.push(new ur(t, o, a, n)),
        (n = null));
        s && s && 9 != s.nodeType;

      ) {
        let t = i ? null : Yp(e),
          o = i ? 1 : 0;
        for (; e; ) 1 == e.nodeType && (o += 2), (e = e.previousSibling);
        r.push(new cr(o, t, n)),
          (n = null),
          (e = s),
          (s = s.parentNode),
          (i = !1);
      }
      r.reverse(),
        this.steps
          ? (r.push(new lr()), (this.steps = r.concat(this.steps)))
          : (this.steps = r);
    }
    toString() {
      if (!this.steps) return "";
      let e = new $e();
      e.append("epubcfi(");
      for (let t = 0; t < this.steps.length; t++) this.steps[t].appendTo(e);
      return e.append(")"), e.toString().replace(/%/g, "%25");
    }
  };
function Ol() {
  return {
    fontFamily: "serif",
    lineHeight: 1.25,
    margin: 8,
    hyphenate: !1,
    columnWidth: 25,
    horizontal: !1,
    nightMode: !1,
    spreadView: !1,
    pageBorder: 1,
    enabledMediaTypes: { vivliostyle: !0, print: !0 },
    defaultPaperSize: void 0,
  };
}
function vr(e) {
  return {
    fontFamily: e.fontFamily,
    lineHeight: e.lineHeight,
    margin: e.margin,
    hyphenate: e.hyphenate,
    columnWidth: e.columnWidth,
    horizontal: e.horizontal,
    nightMode: e.nightMode,
    spreadView: e.spreadView,
    pageBorder: e.pageBorder,
    enabledMediaTypes: Object.assign({}, e.enabledMediaTypes),
    defaultPaperSize: e.defaultPaperSize
      ? Object.assign({}, e.defaultPaperSize)
      : void 0,
  };
}
var am = Ol(),
  qp = { PENDING: {} };
function Dl(e, t, i, n) {
  let r = Math.min((e - 0) / i, (t - 0) / n);
  return `matrix(${r},0,0,${r},0,0)`;
}
function Bl(e) {
  return `"${cs(`${e}`)}"`;
}
function lm(e) {
  return Kt(`${e}`);
}
function jo(e, t) {
  return e ? `${Kt(e)}.${Kt(t)}` : Kt(t);
}
var Kp = 0,
  us = class {
    constructor(e, t) {
      if (
        ((this.parent = e),
        (this.resolver = t),
        p(this, "scopeKey"),
        p(this, "children", []),
        p(this, "zero"),
        p(this, "one"),
        p(this, "_true"),
        p(this, "_false"),
        p(this, "values", {}),
        p(this, "funcs", {}),
        p(this, "builtIns", {}),
        (this.scopeKey = "S" + Kp++),
        (this.zero = new ie(this, 0)),
        (this.one = new ie(this, 1)),
        (this._true = new ie(this, !0)),
        (this._false = new ie(this, !1)),
        e && e.children.push(this),
        !e)
      ) {
        let e = this.builtIns;
        (e.floor = Math.floor),
          (e.ceil = Math.ceil),
          (e.round = Math.round),
          (e.sqrt = Math.sqrt),
          (e.min = Math.min),
          (e.max = Math.max),
          (e.letterbox = Dl),
          (e["css-string"] = Bl),
          (e["css-name"] = lm),
          (e.typeof = (e) => typeof e),
          this.defineBuiltInName("page-width", function () {
            return this.pageWidth();
          }),
          this.defineBuiltInName("page-height", function () {
            return this.pageHeight();
          }),
          this.defineBuiltInName("pref-font-family", function () {
            return this.pref.fontFamily;
          }),
          this.defineBuiltInName("pref-night-mode", function () {
            return this.pref.nightMode;
          }),
          this.defineBuiltInName("pref-hyphenate", function () {
            return this.pref.hyphenate;
          }),
          this.defineBuiltInName("pref-margin", function () {
            return this.pref.margin;
          }),
          this.defineBuiltInName("pref-line-height", function () {
            return this.pref.lineHeight;
          }),
          this.defineBuiltInName("pref-column-width", function () {
            return this.pref.columnWidth * this.fontSize;
          }),
          this.defineBuiltInName("pref-horizontal", function () {
            return this.pref.horizontal;
          }),
          this.defineBuiltInName("pref-spread-view", function () {
            return this.pref.spreadView;
          }),
          this.defineBuiltInName("pub-title", function () {
            return Bl(this.pubTitle ? this.pubTitle : "");
          }),
          this.defineBuiltInName("doc-title", function () {
            return Bl(this.docTitle ? this.docTitle : "");
          });
      }
    }
    defineBuiltInName(e, t) {
      this.values[e] = new Z(this, t, e);
    }
    defineName(e, t) {
      this.values[e] = t;
    }
    defineFunc(e, t) {
      this.funcs[e] = t;
    }
    defineBuiltIn(e, t) {
      this.builtIns[e] = t;
    }
  };
function Zp(e) {
  switch (null == e ? void 0 : e.toLowerCase()) {
    case "px":
    case "in":
    case "pt":
    case "pc":
    case "cm":
    case "mm":
    case "q":
      return !0;
    default:
      return !1;
  }
}
function Ml(e) {
  switch (null == e ? void 0 : e.toLowerCase()) {
    case "vw":
    case "vh":
    case "vi":
    case "vb":
    case "vmin":
    case "vmax":
    case "pvw":
    case "pvh":
    case "pvi":
    case "pvb":
    case "pvmin":
    case "pvmax":
      return !0;
    default:
      return !1;
  }
}
function Qp(e) {
  switch (null == e ? void 0 : e.toLowerCase()) {
    case "em":
    case "ex":
    case "rem":
    case "lh":
    case "rlh":
      return !0;
    default:
      return !1;
  }
}
function Jp(e) {
  switch (null == e ? void 0 : e.toLowerCase()) {
    case "rem":
    case "rlh":
      return !0;
    default:
      return !1;
  }
}
var Y = {
  px: 1,
  in: 96,
  pt: 4 / 3,
  pc: 16,
  cm: 96 / 2.54,
  mm: 96 / 25.4,
  q: 96 / 2.54 / 40,
  em: 16,
  rem: 16,
  ex: 8,
  lh: 20,
  rlh: 20,
  dppx: 1,
  dpi: 1 / 96,
  dpcm: 2.54 / 96,
};
function Tr(e) {
  switch (e) {
    case "q":
      return !CSS.supports("font-size", "1q");
    case "lh":
      return !CSS.supports("line-height", "1lh");
    case "rem":
    case "rlh":
      return !0;
    default:
      return !1;
  }
}
var js = class {
    constructor(e, t, i, n) {
      (this.rootScope = e),
        (this.viewportWidth = t),
        (this.viewportHeight = i),
        p(this, "actualPageWidth", null),
        p(this, "pageWidth"),
        p(this, "actualPageHeight", null),
        p(this, "pageHeight"),
        p(this, "initialFontSize"),
        p(this, "rootFontSize", null),
        p(this, "isRelativeRootFontSize", null),
        p(this, "fontSize"),
        p(this, "rootLineHeight", null),
        p(this, "pref"),
        p(this, "scopes", {}),
        p(this, "pageAreaWidth", null),
        p(this, "pageAreaHeight", null),
        p(this, "pageVertical", null),
        p(this, "pubTitle", null),
        p(this, "docTitle", null),
        (this.pageWidth = function () {
          return this.actualPageWidth
            ? this.actualPageWidth
            : this.pref.spreadView
            ? Math.floor(t / 2) - this.pref.pageBorder
            : t;
        }),
        (this.pageHeight = function () {
          return this.actualPageHeight ? this.actualPageHeight : i;
        }),
        (this.initialFontSize = n),
        (this.fontSize = function () {
          return this.rootFontSize ? this.rootFontSize : n;
        }),
        (this.pref = am);
    }
    getScopeContext(e) {
      let t = this.scopes[e.scopeKey];
      return t || ((t = {}), (this.scopes[e.scopeKey] = t)), t;
    }
    clearScope(e) {
      this.scopes[e.scopeKey] = {};
      for (let t = 0; t < e.children.length; t++)
        this.clearScope(e.children[t]);
    }
    queryUnitSize(e, t, i) {
      if (Ml(e)) {
        let t = this.pageWidth() / 100,
          n = this.pageHeight() / 100,
          r = null != this.pageAreaWidth ? this.pageAreaWidth / 100 : t,
          s = null != this.pageAreaHeight ? this.pageAreaHeight / 100 : n,
          o = null != i ? i : this.pageVertical;
        switch (e) {
          case "vw":
            return r;
          case "vh":
            return s;
          case "vi":
            return o ? s : r;
          case "vb":
            return o ? r : s;
          case "vmin":
            return r < s ? r : s;
          case "vmax":
            return r > s ? r : s;
          case "pvw":
            return t;
          case "pvh":
            return n;
          case "pvi":
            return o ? n : t;
          case "pvb":
            return o ? t : n;
          case "pvmin":
            return t < n ? t : n;
          case "pvmax":
            return t > n ? t : n;
        }
      }
      return "em" == e || "rem" == e
        ? t
          ? this.initialFontSize
          : this.fontSize()
        : "ex" == e
        ? (Y.ex * (t ? this.initialFontSize : this.fontSize())) / Y.em
        : "lh" == e || "rlh" == e
        ? this.rootLineHeight
        : Y[e];
    }
    evalName(e, t) {
      do {
        let i = e.values[t];
        if (i || (e.resolver && ((i = e.resolver.call(this, t, !1)), i)))
          return i;
        e = e.parent;
      } while (e);
      throw new Error(`Name '${t}' is undefined`);
    }
    evalCall(e, t, i, n) {
      do {
        let r = e.funcs[t];
        if (r || (e.resolver && ((r = e.resolver.call(this, t, !0)), r)))
          return r;
        let s = e.builtIns[t];
        if (s) {
          if (n) return e.zero;
          let t = Array(i.length);
          for (let e = 0; e < i.length; e++) t[e] = i[e].evaluate(this);
          return new ie(e, s.apply(this, t));
        }
        e = e.parent;
      } while (e);
      throw new Error(`Function '${t}' is undefined`);
    }
    evalMediaName(e, t) {
      let i = "all" === e || !!this.pref.enabledMediaTypes[e];
      return t ? !i : i;
    }
    evalMediaTest(e, t) {
      let i = "",
        n = e.match(/^(min|max)-(.*)$/);
      n && ((i = n[1]), (e = n[2]));
      let r = null,
        s = null;
      switch (e) {
        case "width":
        case "height":
        case "device-width":
        case "device-height":
        case "color":
          t && (r = t.evaluate(this));
      }
      switch (e) {
        case "width":
          s = this.pageWidth();
          break;
        case "height":
          s = this.pageHeight();
          break;
        case "device-width":
          s = window.screen.availWidth;
          break;
        case "device-height":
          s = window.screen.availHeight;
          break;
        case "color":
          s = window.screen.pixelDepth;
      }
      if (null != s && null != r)
        switch (i) {
          case "min":
            return s >= Number(r);
          case "max":
            return s <= Number(r);
          default:
            return s == r;
        }
      else if (null != s && null == t) return 0 !== s;
      return !1;
    }
    evalSupportsTest(e, t, i) {
      return !1;
    }
    queryVal(e, t) {
      let i = e && this.scopes[e.scopeKey];
      return i ? i[t] : void 0;
    }
    storeVal(e, t, i) {
      this.getScopeContext(e)[t] = i;
    }
  },
  lt = class {
    constructor(e) {
      (this.scope = e),
        p(this, "key"),
        (this.scope = e),
        (this.key = "_" + Kp++);
    }
    toString() {
      let e = new $e();
      return this.appendTo(e, 0), e.toString();
    }
    appendTo(e, t) {
      throw new Error("F_ABSTRACT");
    }
    evaluateCore(e) {
      throw new Error("F_ABSTRACT");
    }
    expand(e, t) {
      return this;
    }
    dependCore(e, t, i) {
      return e === this;
    }
    dependOuter(e, t, i) {
      let n = i[this.key];
      if (null != n) return n !== qp.PENDING && n;
      {
        i[this.key] = qp.PENDING;
        let n = this.dependCore(e, t, i);
        return (i[this.key] = n), n;
      }
    }
    depend(e, t) {
      return this.dependOuter(e, t, {});
    }
    evaluate(e) {
      let t = e.queryVal(this.scope, this.key);
      return (
        void 0 !== t ||
          ((t = this.evaluateCore(e)),
          this.scope && e.storeVal(this.scope, this.key, t)),
        t
      );
    }
    isMediaName() {
      return !1;
    }
  },
  dr = class extends lt {
    constructor(e, t) {
      super(e), (this.val = t);
    }
    getOp() {
      throw new Error("F_ABSTRACT");
    }
    evalPrefix(e) {
      throw new Error("F_ABSTRACT");
    }
    evaluateCore(e) {
      let t = this.val.evaluate(e);
      return this.evalPrefix(t);
    }
    dependCore(e, t, i) {
      return e === this || this.val.dependOuter(e, t, i);
    }
    appendTo(e, t) {
      10 < t && e.append("("),
        e.append(this.getOp()),
        this.val.appendTo(e, 10),
        10 < t && e.append(")");
    }
    expand(e, t) {
      let i = this.val.expand(e, t);
      return i === this.val ? this : new this.constructor(this.scope, i);
    }
  },
  Ys = class extends lt {
    constructor(e, t, i) {
      super(e), (this.lhs = t), (this.rhs = i);
    }
    getPriority() {
      throw new Error("F_ABSTRACT");
    }
    getOp() {
      throw new Error("F_ABSTRACT");
    }
    evalInfix(e, t) {
      throw new Error("F_ABSTRACT");
    }
    evaluateCore(e) {
      let t = this.lhs.evaluate(e),
        i = this.rhs.evaluate(e);
      return this.evalInfix(t, i);
    }
    dependCore(e, t, i) {
      return (
        e === this ||
        this.lhs.dependOuter(e, t, i) ||
        this.rhs.dependOuter(e, t, i)
      );
    }
    appendTo(e, t) {
      let i = this.getPriority();
      i <= t && e.append("("),
        this.lhs.appendTo(e, i),
        e.append(this.getOp()),
        this.rhs.appendTo(e, i),
        i <= t && e.append(")");
    }
    expand(e, t) {
      let i = this.lhs.expand(e, t),
        n = this.rhs.expand(e, t);
      return i === this.lhs && n === this.rhs
        ? this
        : new this.constructor(this.scope, i, n);
    }
  },
  pr = class extends Ys {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getPriority() {
      return 1;
    }
  },
  Ln = class extends Ys {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getPriority() {
      return 2;
    }
  },
  hr = class extends Ys {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getPriority() {
      return 3;
    }
  },
  Ho = class extends Ys {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getPriority() {
      return 4;
    }
  },
  mt = class extends dr {
    constructor(e, t) {
      super(e, t);
    }
    getOp() {
      return "!";
    }
    evalPrefix(e) {
      return !e;
    }
  },
  fr = class extends mt {
    constructor(e, t) {
      super(e, t);
    }
    getOp() {
      return "not ";
    }
  },
  Qt = class extends dr {
    constructor(e, t) {
      super(e, t);
    }
    getOp() {
      return "-";
    }
    evalPrefix(e) {
      return -e;
    }
  },
  qs = class extends pr {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "&&";
    }
    evaluateCore(e) {
      return this.lhs.evaluate(e) && this.rhs.evaluate(e);
    }
  },
  gr = class extends qs {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return " and ";
    }
  },
  Ks = class extends pr {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "||";
    }
    evaluateCore(e) {
      return this.lhs.evaluate(e) || this.rhs.evaluate(e);
    }
  },
  mr = class extends Ks {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return ", ";
    }
  },
  Cr = class extends Ks {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return " or ";
    }
  },
  br = class extends Ln {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "<";
    }
    evalInfix(e, t) {
      return e < t;
    }
  },
  xr = class extends Ln {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "<=";
    }
    evalInfix(e, t) {
      return e <= t;
    }
  },
  yr = class extends Ln {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return ">";
    }
    evalInfix(e, t) {
      return e > t;
    }
  },
  ds = class extends Ln {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return ">=";
    }
    evalInfix(e, t) {
      return e >= t;
    }
  },
  Rn = class extends Ln {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "==";
    }
    evalInfix(e, t) {
      return e == t;
    }
  },
  Er = class extends Ln {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "!=";
    }
    evalInfix(e, t) {
      return e != t;
    }
  },
  zo = class extends hr {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "+";
    }
    evalInfix(e, t) {
      return e + t;
    }
  },
  Go = class extends hr {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return " - ";
    }
    evalInfix(e, t) {
      return e - t;
    }
  },
  ps = class extends Ho {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "*";
    }
    evalInfix(e, t) {
      return e * t;
    }
  },
  Wo = class extends Ho {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "/";
    }
    evalInfix(e, t) {
      return e / t;
    }
  },
  Zs = class extends Ho {
    constructor(e, t, i) {
      super(e, t, i);
    }
    getOp() {
      return "%";
    }
    evalInfix(e, t) {
      return e % t;
    }
  },
  Ot = class extends lt {
    constructor(e, t, i) {
      var n;
      super(e),
        (this.num = t),
        p(this, "unit"),
        (this.unit =
          null != (n = null == i ? void 0 : i.toLowerCase()) ? n : "");
    }
    appendTo(e, t) {
      e.append(this.num.toString()), e.append(Kt(this.unit));
    }
    evaluateCore(e) {
      return this.num * e.queryUnitSize(this.unit, !1);
    }
  },
  ee = class extends lt {
    constructor(e, t) {
      super(e), (this.qualifiedName = t);
    }
    appendTo(e, t) {
      e.append(this.qualifiedName);
    }
    evaluateCore(e) {
      return e.evalName(this.scope, this.qualifiedName).evaluate(e);
    }
    dependCore(e, t, i) {
      return (
        e === this ||
        t.evalName(this.scope, this.qualifiedName).dependOuter(e, t, i)
      );
    }
  },
  $o = class extends lt {
    constructor(e, t, i) {
      super(e), (this.not = t), (this.name = i);
    }
    appendTo(e, t) {
      this.not && e.append("not "), e.append(Kt(this.name));
    }
    evaluateCore(e) {
      return e.evalMediaName(this.name, this.not);
    }
    isMediaName() {
      return !0;
    }
  },
  Z = class extends lt {
    constructor(e, t, i) {
      super(e), (this.fn = t), (this.str = i);
    }
    appendTo(e, t) {
      e.append(this.str);
    }
    evaluateCore(e) {
      return this.fn.call(e);
    }
  };
function cm(e, t) {
  e.append("(");
  for (let i = 0; i < t.length; i++) i && e.append(","), t[i].appendTo(e, 0);
  e.append(")");
}
function um(e, t, i) {
  let n = t;
  for (let r = 0; r < t.length; r++) {
    let s = t[r].expand(e, i);
    if (t !== n) n[r] = s;
    else if (s !== t[r]) {
      n = Array(t.length);
      for (let e = 0; e < r; e++) n[e] = t[e];
      n[r] = s;
    }
  }
  return n;
}
var hn = class e extends lt {
    constructor(e, t, i) {
      super(e), (this.qualifiedName = t), (this.params = i);
    }
    appendTo(e, t) {
      e.append(this.qualifiedName), cm(e, this.params);
    }
    evaluateCore(e) {
      return e
        .evalCall(this.scope, this.qualifiedName, this.params, !1)
        .expand(e, this.params)
        .evaluate(e);
    }
    dependCore(e, t, i) {
      if (e === this) return !0;
      for (let n = 0; n < this.params.length; n++)
        if (this.params[n].dependOuter(e, t, i)) return !0;
      return t
        .evalCall(this.scope, this.qualifiedName, this.params, !0)
        .dependOuter(e, t, i);
    }
    expand(t, i) {
      let n = um(t, this.params, i);
      return n === this.params
        ? this
        : new e(this.scope, this.qualifiedName, n);
    }
  },
  Sr = class e extends lt {
    constructor(e, t, i, n) {
      super(e), (this.cond = t), (this.ifTrue = i), (this.ifFalse = n);
    }
    appendTo(e, t) {
      t > 0 && e.append("("),
        this.cond.appendTo(e, 0),
        e.append("?"),
        this.ifTrue.appendTo(e, 0),
        e.append(":"),
        this.ifFalse.appendTo(e, 0),
        t > 0 && e.append(")");
    }
    evaluateCore(e) {
      return this.cond.evaluate(e)
        ? this.ifTrue.evaluate(e)
        : this.ifFalse.evaluate(e);
    }
    dependCore(e, t, i) {
      return (
        e === this ||
        this.cond.dependOuter(e, t, i) ||
        this.ifTrue.dependOuter(e, t, i) ||
        this.ifFalse.dependOuter(e, t, i)
      );
    }
    expand(t, i) {
      let n = this.cond.expand(t, i),
        r = this.ifTrue.expand(t, i),
        s = this.ifFalse.expand(t, i);
      return n === this.cond && r === this.ifTrue && s === this.ifFalse
        ? this
        : new e(this.scope, n, r, s);
    }
  },
  ie = class extends lt {
    constructor(e, t) {
      super(e), (this.val = t);
    }
    appendTo(e, t) {
      switch (typeof this.val) {
        case "number":
        case "boolean":
          e.append(this.val.toString());
          break;
        case "string":
          e.append('"'), e.append(cs(this.val)), e.append('"');
          break;
        default:
          throw new Error("F_UNEXPECTED_STATE");
      }
    }
    evaluateCore(e) {
      return this.val;
    }
  },
  Xo = class e extends lt {
    constructor(e, t, i) {
      super(e), (this.name = t), (this.value = i);
    }
    appendTo(e, t) {
      e.append("("),
        e.append(cs(this.name.name)),
        e.append(":"),
        this.value.appendTo(e, 0),
        e.append(")");
    }
    evaluateCore(e) {
      return e.evalMediaTest(this.name.name, this.value);
    }
    dependCore(e, t, i) {
      return e === this || this.value.dependOuter(e, t, i);
    }
    expand(t, i) {
      let n = this.value.expand(t, i);
      return n === this.value ? this : new e(this.scope, this.name, n);
    }
  },
  Nr = class extends lt {
    constructor(e, t, i, n) {
      super(e), (this.name = t), (this.value = i), (this.isFunc = n);
    }
    appendTo(e, t) {
      this.isFunc && e.append(this.name),
        e.append("("),
        !this.isFunc && this.name && (e.append(this.name), e.append(":")),
        e.append(this.value),
        e.append(")");
    }
    evaluateCore(e) {
      return e.evalSupportsTest(this.name, this.value, this.isFunc);
    }
  },
  Qs = class extends lt {
    constructor(e, t) {
      super(e), (this.index = t);
    }
    appendTo(e, t) {
      e.append("$"), e.append(this.index.toString());
    }
    expand(e, t) {
      let i = t[this.index];
      if (!i) throw new Error(`Parameter missing: ${this.index}`);
      return i;
    }
  };
function tt(e, t, i) {
  return t === e._false || t === e.zero || i == e._false || i == e.zero
    ? e._false
    : t === e._true || t === e.one
    ? i
    : i === e._true || i === e.one
    ? t
    : new qs(e, t, i);
}
function H(e, t, i) {
  return t === e.zero ? i : i === e.zero ? t : new zo(e, t, i);
}
function K(e, t, i) {
  return t === e.zero ? new Qt(e, i) : i === e.zero ? t : new Go(e, t, i);
}
function Js(e, t, i) {
  return t === e.zero || i === e.zero
    ? e.zero
    : t === e.one
    ? i
    : i === e.one
    ? t
    : new ps(e, t, i);
}
function Yo(e, t, i) {
  return t === e.zero ? e.zero : i === e.one ? t : new Wo(e, t, i);
}
var ct = class {
    visitValues(e) {
      for (let t = 0; t < e.length; t++) e[t].visit(this);
      return null;
    }
    visitEmpty(e) {
      return null;
    }
    visitSlash(e) {
      return null;
    }
    visitStr(e) {
      return null;
    }
    visitIdent(e) {
      return null;
    }
    visitNumeric(e) {
      return null;
    }
    visitNum(e) {
      return null;
    }
    visitInt(e) {
      return this.visitNum(e);
    }
    visitHexColor(e) {
      return null;
    }
    visitURL(e) {
      return null;
    }
    visitURange(e) {
      return null;
    }
    visitSpaceList(e) {
      return this.visitValues(e.values), null;
    }
    visitCommaList(e) {
      return this.visitValues(e.values), null;
    }
    visitFunc(e) {
      return this.visitValues(e.values), null;
    }
    visitExpr(e) {
      return null;
    }
  },
  Jt = class extends ct {
    constructor() {
      super(), p(this, "error", !1);
    }
    visitValues(e) {
      let t = null;
      for (let i = 0; i < e.length; i++) {
        let n = e[i],
          r = n.visit(this);
        if (this.error) return [];
        if (t) t[i] = r;
        else if (n !== r) {
          t = new Array(e.length);
          for (let n = 0; n < i; n++) t[n] = e[n];
          t[i] = r;
        }
      }
      return t || e;
    }
    visitEmpty(e) {
      return e;
    }
    visitStr(e) {
      return e;
    }
    visitIdent(e) {
      return e;
    }
    visitSlash(e) {
      return e;
    }
    visitNumeric(e) {
      return e;
    }
    visitNum(e) {
      return e;
    }
    visitInt(e) {
      return e;
    }
    visitHexColor(e) {
      return e;
    }
    visitURL(e) {
      return e;
    }
    visitURange(e) {
      return e;
    }
    visitSpaceList(e) {
      let t = this.visitValues(e.values);
      return this.error ? O : t === e.values ? e : new q(t);
    }
    visitCommaList(e) {
      let t = this.visitValues(e.values);
      return this.error ? O : t === e.values ? e : new ge(t);
    }
    visitFunc(e) {
      let t = this.visitValues(e.values);
      return this.error ? O : t === e.values ? e : new At(e.name, t);
    }
    visitExpr(e) {
      return e;
    }
  },
  Se = class {
    toString() {
      let e = new $e();
      return this.appendTo(e, !0), e.toString();
    }
    stringValue() {
      let e = new $e();
      return this.appendTo(e, !1), e.toString();
    }
    toExpr(e, t) {
      return null;
    }
    appendTo(e, t) {
      e.append("[error]");
    }
    isExpr() {
      return !1;
    }
    isNumeric() {
      return !1;
    }
    isNum() {
      return !1;
    }
    isIdent() {
      return !1;
    }
    isSpaceList() {
      return !1;
    }
    visit(e) {
      return this;
    }
  },
  wr = class e extends Se {
    static get instance() {
      return this.empty || (this.empty = new e()), this.empty;
    }
    constructor() {
      super();
    }
    toExpr(e, t) {
      return new ie(e, "");
    }
    appendTo(e, t) {}
    visit(e) {
      return e.visitEmpty(this);
    }
  };
p(wr, "empty");
var _l = wr,
  O = _l.instance,
  Pr = class e extends Se {
    static get instance() {
      return this.slash || (this.slash = new e()), this.slash;
    }
    constructor() {
      super();
    }
    toExpr(e, t) {
      return new ie(e, "/");
    }
    appendTo(e, t) {
      e.append("/");
    }
    visit(e) {
      return e.visitSlash(this);
    }
  };
p(Pr, "slash");
var Ul = Pr,
  to = Ul.instance,
  ue = class extends Se {
    constructor(e) {
      super(), (this.str = e);
    }
    toExpr(e, t) {
      return new ie(e, this.str);
    }
    appendTo(e, t) {
      t
        ? (e.append('"'), e.append(cs(this.str)), e.append('"'))
        : e.append(this.str);
    }
    visit(e) {
      return e.visitStr(this);
    }
  },
  Hl = {},
  be = class extends Se {
    constructor(e) {
      if ((super(), (this.name = e), Hl[e])) throw new Error("E_INVALID_CALL");
      Hl[e] = this;
    }
    toExpr(e, t) {
      return new ie(e, this.name);
    }
    appendTo(e, t) {
      t ? e.append(Kt(this.name)) : e.append(this.name);
    }
    visit(e) {
      return e.visitIdent(this);
    }
    isIdent() {
      return !0;
    }
  };
function L(e) {
  let t = Hl[e];
  return t || (t = new be(e)), t;
}
var P = class extends Se {
    constructor(e, t) {
      var i;
      super(),
        (this.num = e),
        p(this, "unit"),
        (this.unit =
          null != (i = null == t ? void 0 : t.toLowerCase()) ? i : "");
    }
    toExpr(e, t) {
      return 0 == this.num
        ? e.zero
        : t && "%" == this.unit
        ? 100 == this.num
          ? t
          : new ps(e, t, new ie(e, this.num / 100))
        : new Ot(e, this.num, this.unit);
    }
    appendTo(e, t) {
      e.append(this.num.toString()), e.append(this.unit);
    }
    visit(e) {
      return e.visitNumeric(this);
    }
    isNumeric() {
      return !0;
    }
  },
  nt = class extends Se {
    constructor(e) {
      super(), (this.num = e);
    }
    toExpr(e, t) {
      return 0 == this.num
        ? e.zero
        : 1 == this.num
        ? e.one
        : new ie(e, this.num);
    }
    appendTo(e, t) {
      e.append(this.num.toString());
    }
    visit(e) {
      return e.visitNum(this);
    }
    isNum() {
      return !0;
    }
  },
  ut = class extends nt {
    constructor(e) {
      super(e);
    }
    visit(e) {
      return e.visitInt(this);
    }
  },
  eo = class extends Se {
    constructor(e) {
      super(), (this.hex = e);
    }
    appendTo(e, t) {
      e.append("#"), e.append(this.hex);
    }
    visit(e) {
      return e.visitHexColor(this);
    }
  },
  Oe = class extends Se {
    constructor(e) {
      super(), (this.url = e);
    }
    appendTo(e, t) {
      e.append('url("'), e.append(cs(this.url)), e.append('")');
    }
    visit(e) {
      return e.visitURL(this);
    }
  },
  qo = class extends Se {
    constructor(e) {
      super(), (this.urangeText = e);
    }
    appendTo(e, t) {
      e.append(this.urangeText);
    }
    visit(e) {
      return e.visitURange(this);
    }
  };
function zl(e, t, i, n) {
  var r, s;
  let o = t.length;
  if (o > 0) {
    null == (r = t[0]) || r.appendTo(e, n);
    for (let r = 1; r < o; r++)
      e.append(i), null == (s = t[r]) || s.appendTo(e, n);
  }
}
var q = class extends Se {
    constructor(e) {
      super(), (this.values = e);
    }
    appendTo(e, t) {
      zl(e, this.values, " ", t);
    }
    visit(e) {
      return e.visitSpaceList(this);
    }
    isSpaceList() {
      return !0;
    }
  },
  ge = class extends Se {
    constructor(e) {
      super(), (this.values = e);
    }
    appendTo(e, t) {
      zl(e, this.values, ",", t);
    }
    visit(e) {
      return e.visitCommaList(this);
    }
  },
  At = class extends Se {
    constructor(e, t) {
      super(), (this.name = e), (this.values = t);
    }
    appendTo(e, t) {
      e.append(Kt(this.name)),
        e.append("("),
        zl(e, this.values, ",", t),
        e.append(")");
    }
    visit(e) {
      return e.visitFunc(this);
    }
  },
  F = class extends Se {
    constructor(e) {
      super(), (this.expr = e);
    }
    toExpr() {
      return this.expr;
    }
    appendTo(e, t) {
      this.expr instanceof ie || this.expr instanceof Ot
        ? this.expr.appendTo(e, 0)
        : (e.append("-epubx-expr("), this.expr.appendTo(e, 0), e.append(")"));
    }
    visit(e) {
      return e.visitExpr(this);
    }
    isExpr() {
      return !0;
    }
  },
  In = class extends Se {
    constructor(e) {
      super(), (this.text = e);
    }
    appendTo(e, t) {
      e.append(this.text || " ");
    }
  };
function ke(e, t) {
  if (e) {
    if (e.isNumeric()) {
      let i = e;
      return t.queryUnitSize(i.unit, !1) * i.num;
    }
    if (e.isNum()) return e.num;
  }
  return 0;
}
function kr(e, t) {
  return new P(ke(e, t), "px");
}
var b = {
    absolute: L("absolute"),
    all: L("all"),
    always: L("always"),
    anywhere: L("anywhere"),
    auto: L("auto"),
    avoid: L("avoid"),
    balance: L("balance"),
    balance_all: L("balance-all"),
    block: L("block"),
    block_end: L("block-end"),
    block_start: L("block-start"),
    both: L("both"),
    bottom: L("bottom"),
    border_box: L("border-box"),
    break_all: L("break-all"),
    break_word: L("break-word"),
    clip: L("clip"),
    crop: L("crop"),
    cross: L("cross"),
    column: L("column"),
    discard: L("discard"),
    exclusive: L("exclusive"),
    _false: L("false"),
    fixed: L("fixed"),
    flex: L("flex"),
    flow_root: L("flow-root"),
    footnote: L("footnote"),
    footer: L("footer"),
    grid: L("grid"),
    header: L("header"),
    hidden: L("hidden"),
    horizontal_tb: L("horizontal-tb"),
    inherit: L("inherit"),
    initial: L("initial"),
    inline: L("inline"),
    inline_block: L("inline-block"),
    inline_end: L("inline-end"),
    inline_start: L("inline-start"),
    inside: L("inside"),
    keep: L("keep"),
    landscape: L("landscape"),
    left: L("left"),
    line: L("line"),
    list_item: L("list-item"),
    ltr: L("ltr"),
    manual: L("manual"),
    max_content: L("max-content"),
    min_content: L("min-content"),
    none: L("none"),
    normal: L("normal"),
    oeb_page_foot: L("oeb-page-foot"),
    oeb_page_head: L("oeb-page-head"),
    outside: L("outside"),
    padding_box: L("padding-box"),
    page: L("page"),
    relative: L("relative"),
    revert: L("revert"),
    right: L("right"),
    same: L("same"),
    scale: L("scale"),
    snap_block: L("snap-block"),
    snap_inline: L("snap-inline"),
    solid: L("solid"),
    spread: L("spread"),
    _static: L("static"),
    rtl: L("rtl"),
    table: L("table"),
    table_caption: L("table-caption"),
    table_cell: L("table-cell"),
    table_footer_group: L("table-footer-group"),
    table_header_group: L("table-header-group"),
    table_row: L("table-row"),
    top: L("top"),
    transparent: L("transparent"),
    unset: L("unset"),
    vertical_lr: L("vertical-lr"),
    vertical_rl: L("vertical-rl"),
    visible: L("visible"),
    _true: L("true"),
  },
  Gl = new P(100, "%"),
  Vn = new P(100, "pvw"),
  Fn = new P(100, "pvh"),
  ne = new P(0, "px"),
  qb = new qo("U+0-10FFFF"),
  eh = { "font-size": 1, "line-height": 2, color: 3 };
function M(e) {
  return e === b.inherit || e === b.initial || e === b.revert || e === b.unset;
}
function Wl(e, t) {
  return (eh[e] || Number.MAX_VALUE) - (eh[t] || Number.MAX_VALUE);
}
function bt(e) {
  return (null == e ? void 0 : e.length) > 2 && e.startsWith("--");
}
var He = class {
    constructor(e, t, i, n) {
      (this.x1 = e), (this.y1 = t), (this.x2 = i), (this.y2 = n);
    }
  },
  Dt = class {
    constructor(e, t) {
      (this.x = e), (this.y = t);
    }
  },
  so = class {
    constructor(e, t, i, n) {
      (this.left = e), (this.top = t), (this.right = i), (this.bottom = n);
    }
  },
  Ar = class {
    constructor(e, t, i, n) {
      (this.low = e), (this.high = t), (this.winding = i), (this.shapeId = n);
    }
  },
  Mt = class {
    constructor(e, t, i, n) {
      (this.y1 = e),
        (this.y2 = t),
        (this.x1 = i),
        (this.x2 = n),
        p(this, "left", null),
        p(this, "right", null);
    }
  };
function dm(e, t) {
  return e.low.y - t.low.y || e.low.x - t.low.x;
}
var hs = class e {
  constructor(e) {
    this.points = e;
  }
  addSegments(e, t) {
    let i = this.points,
      n = i.length,
      r = i[n - 1];
    for (let s = 0; s < n; s++) {
      let n,
        o = i[s];
      (n = r.y < o.y ? new Ar(r, o, 1, t) : new Ar(o, r, -1, t)),
        e.push(n),
        (r = o);
    }
  }
  withOffset(t, i) {
    let n = [];
    for (let e of this.points) n.push(new Dt(e.x + t, e.y + i));
    return new e(n);
  }
};
function $l(e, t, i, n) {
  let r = [];
  for (let s = 0; s < 20; s++) {
    let o = (2 * s * Math.PI) / 20;
    r.push(new Dt(e + i * Math.sin(o), t + n * Math.cos(o)));
  }
  return new hs(r);
}
function Ko(e, t, i, n) {
  return new hs([new Dt(e, t), new Dt(i, t), new Dt(i, n), new Dt(e, n)]);
}
var no = class {
  constructor(e, t, i, n) {
    (this.x = e), (this.winding = t), (this.shapeId = i), (this.lowOrHigh = n);
  }
};
function th(e, t) {
  let i =
    e.low.x + ((e.high.x - e.low.x) * (t - e.low.y)) / (e.high.y - e.low.y);
  if (isNaN(i)) throw new Error("Bad intersection");
  return i;
}
function pm(e, t, i, n) {
  let r, s, o, a;
  t.high.y < i && V.warn("Error: inconsistent segment (1)"),
    t.low.y <= i ? ((r = th(t, i)), (s = t.winding)) : ((r = t.low.x), (s = 0)),
    t.high.y >= n
      ? ((o = th(t, n)), (a = t.winding))
      : ((o = t.high.x), (a = 0)),
    r < o
      ? (e.push(new no(r, s, t.shapeId, -1)),
        e.push(new no(o, a, t.shapeId, 1)))
      : (e.push(new no(o, a, t.shapeId, -1)),
        e.push(new no(r, s, t.shapeId, 1)));
}
function hm(e, t, i) {
  let n,
    r = t + i,
    s = Array(r),
    o = Array(r);
  for (n = 0; n <= r; n++) (s[n] = 0), (o[n] = 0);
  let a = [],
    l = !1,
    h = e.length;
  for (let i = 0; i < h; i++) {
    let h = e[i];
    (s[h.shapeId] += h.winding), (o[h.shapeId] += h.lowOrHigh);
    let u = !1;
    for (n = 0; n < t; n++)
      if (s[n] && !o[n]) {
        u = !0;
        break;
      }
    if (u)
      for (n = t; n <= r; n++)
        if (s[n] || o[n]) {
          u = !1;
          break;
        }
    l != u && (a.push(h.x), (l = u));
  }
  return a;
}
function fm(e, t) {
  return t ? Math.ceil(e / t) * t : e;
}
function nh(e, t) {
  return t ? Math.floor(e / t) * t : e;
}
function gm(e) {
  return new Dt(e.y, -e.x);
}
function oo(e) {
  return new He(e.y1, -e.x2, e.y2, -e.x1);
}
function Lr(e) {
  return new He(-e.y2, e.x1, -e.y1, e.x2);
}
function sh(e) {
  return new hs(e.points.map((e) => gm(e)));
}
function oh(e, t, i, n, r, s) {
  s && ((e = oo(e)), (t = t.map((e) => sh(e))), (i = i.map((e) => sh(e))));
  let o,
    a,
    l,
    h = t.length,
    u = i ? i.length : 0,
    c = [],
    d = [];
  for (o = 0; o < h; o++) t[o].addSegments(d, o);
  for (o = 0; o < u; o++) i[o].addSegments(d, o + h);
  let p = d.length;
  d.sort(dm);
  let f = 0;
  for (; d[f].shapeId >= h; ) f++;
  let g = d[f].low.y;
  g > e.y1 && c.push(new Mt(e.y1, g, e.x2, e.x2));
  let m = 0,
    w = [];
  for (; m < p && (l = d[m]).low.y < g; ) l.high.y > g && w.push(l), m++;
  for (; m < p || w.length > 0; ) {
    let t = e.y2,
      i = Math.min(fm(Math.ceil(g + n), r), e.y2);
    for (a = 0; a < w.length && t > i; a++)
      (l = w[a]),
        l.low.x == l.high.x
          ? l.high.y < t && (t = Math.max(nh(l.high.y, r), i))
          : l.low.x != l.high.x && (t = i);
    for (t > e.y2 && (t = e.y2); m < p && (l = d[m]).low.y < t; )
      if (l.high.y < g) m++;
      else {
        if (!(l.low.y < i)) {
          let e = nh(l.low.y, r);
          e < t && (t = e);
          break;
        }
        (l.low.y == l.high.y && l.low.y == g) || (w.push(l), (t = i)), m++;
      }
    let s = [];
    for (a = 0; a < w.length; a++) pm(s, w[a], g, t);
    s.sort((e, t) => e.x - t.x || e.lowOrHigh - t.lowOrHigh);
    let o = hm(s, h, u);
    if (0 == o.length) c.push(new Mt(g, t, e.x2, e.x2));
    else {
      let i = 0,
        n = e.x1;
      for (a = 0; a < o.length; a += 2) {
        let t = Math.max(e.x1, o[a]),
          r = Math.min(e.x2, o[a + 1]) - t;
        r > i && ((i = r), (n = t));
      }
      0 == i
        ? c.push(new Mt(g, t, e.x2, e.x2))
        : c.push(new Mt(g, t, Math.max(n, e.x1), Math.min(n + i, e.x2)));
    }
    if (t == e.y2) break;
    for (g = t, a = w.length - 1; a >= 0; a--)
      w[a].high.y <= t && w.splice(a, 1);
  }
  return ih(e, c), c;
}
function ih(e, t) {
  let i = t.length - 1,
    n = new Mt(e.y2, e.y2, e.x1, e.x2);
  for (; i >= 0; ) {
    let e = n;
    (n = t[i]),
      (n.y2 - n.y1 < 1 || (n.x1 == e.x1 && n.x2 == e.x2)) &&
        ((e.y1 = n.y1), t.splice(i, 1), (n = e)),
      i--;
  }
}
function rh(e, t) {
  let i = 0,
    n = e.length;
  for (; i < n; ) {
    let r = Math.floor((i + n) / 2);
    t >= e[r].y2 ? (i = r + 1) : (n = r);
  }
  return i;
}
function ah(e, t) {
  if (!e.length) return t;
  let i,
    n,
    r = t.y1;
  for (
    n = 0;
    n < e.length &&
    ((i = e[n]), !(i.y2 > t.y1 && i.x1 - 0.1 <= t.x1 && i.x2 + 0.1 >= t.x2));
    n++
  )
    r = Math.max(r, i.y2);
  let s = r;
  for (
    ;
    n < e.length &&
    ((i = e[n]), !(i.y1 >= t.y2 || i.x1 - 0.1 > t.x1 || i.x2 + 0.1 < t.x2));
    n++
  )
    s = i.y2;
  return (
    (s = n === e.length ? t.y2 : Math.min(s, t.y2)),
    s <= r ? null : new He(t.x1, r, t.x2, s)
  );
}
function lh(e, t) {
  if (!e.length) return t;
  let i,
    n,
    r = t.y2;
  for (
    n = e.length - 1;
    n >= 0 &&
    ((i = e[n]), !(n === e.length - 1 && i.y2 < t.y2)) &&
    !(i.y1 < t.y2 && i.x1 - 0.1 <= t.x1 && i.x2 + 0.1 >= t.x2);
    n--
  )
    r = Math.min(r, i.y1);
  let s = Math.min(r, i.y2);
  for (
    ;
    n >= 0 &&
    ((i = e[n]), !(i.y2 <= t.y1 || i.x1 - 0.1 > t.x1 || i.x2 + 0.1 < t.x2));
    n--
  )
    s = i.y1;
  return (s = Math.max(s, t.y1)), r <= s ? null : new He(t.x1, s, t.x2, r);
}
function ch(e, t, i, n) {
  let r = i.y1,
    s = i.x2 - i.x1,
    o = i.y2 - i.y1,
    a = rh(t, r);
  for (;;) {
    let l = r + o;
    if (l > e.y2) return !1;
    let h = e.x1,
      u = e.x2;
    for (let e = a; e < t.length && t[e].y1 < l; e++) {
      let i = t[e];
      i.x1 > h && (h = i.x1), i.x2 < u && (u = i.x2);
    }
    if (h + s <= u || a >= t.length)
      return (
        "left" == n
          ? ((i.x1 = h), (i.x2 = h + s))
          : ((i.x1 = u - s), (i.x2 = u)),
        (i.y2 += r - i.y1),
        (i.y1 = r),
        !0
      );
    (r = t[a].y2), a++;
  }
}
function uh(e, t, i, n, r) {
  for (
    n || (n = [new Mt(i.y1, i.y2, i.x1, i.x2)]);
    n.length > 0 && n[0].y2 <= e.y1;

  )
    n.shift();
  if (0 == n.length) return;
  n[0].y1 < e.y1 && (n[0].y1 = e.y1);
  let s,
    o = 0 == t.length ? e.y1 : t[t.length - 1].y2;
  o < e.y2 && t.push(new Mt(o, e.y2, e.x1, e.x2));
  let a = rh(t, n[0].y1);
  for (let i of n) {
    if (a == t.length) break;
    for (
      t[a].y1 < i.y1 &&
      ((s = t[a]),
      a++,
      t.splice(a, 0, new Mt(i.y1, s.y2, s.x1, s.x2)),
      (s.y2 = i.y1));
      a < t.length &&
      ((s = t[a++]),
      s.y2 > i.y2 &&
        (t.splice(a, 0, new Mt(i.y2, s.y2, s.x1, s.x2)), (s.y2 = i.y2)),
      i.x1 != i.x2 &&
        ("left" == r
          ? (s.x1 = Math.min(i.x2, e.x2))
          : (s.x2 = Math.max(i.x1, e.x1))),
      s.y2 != i.y2);

    );
  }
  ih(e, t);
}
var Xl = class extends ct {
  constructor() {
    super(), p(this, "propSet", {});
  }
  visitIdent(e) {
    return (this.propSet[e.name] = !0), e;
  }
  visitSpaceList(e) {
    return this.visitValues(e.values), e;
  }
};
function dh(e) {
  if (e) {
    let t = new Xl();
    try {
      return e.visit(t), t.propSet;
    } catch (e) {
      V.warn(e, "toSet:");
    }
  }
  return {};
}
var jl = class extends ct {
  constructor(e) {
    super(), (this.value = e);
  }
  visitInt(e) {
    return (this.value = e.num), e;
  }
};
function Kl(e, t) {
  if (e) {
    let i = new jl(t);
    try {
      return e.visit(i), i.value;
    } catch (e) {
      V.warn(e, "toInt: ");
    }
  }
  return t;
}
var Yl = class extends ct {
  constructor() {
    super(),
      p(this, "collect", !1),
      p(this, "coords", []),
      p(this, "name", null);
  }
  visitNumeric(e) {
    return this.collect && this.coords.push(e), null;
  }
  visitNum(e) {
    return this.collect && 0 == e.num && this.coords.push(new P(0, "px")), null;
  }
  visitSpaceList(e) {
    return this.visitValues(e.values), null;
  }
  visitFunc(e) {
    return (
      this.collect ||
        ((this.collect = !0),
        this.visitValues(e.values),
        (this.collect = !1),
        (this.name = e.name.toLowerCase())),
      null
    );
  }
  getShape(e, t, i, n, r) {
    if (this.coords.length > 0) {
      let s = [];
      switch (
        (this.coords.forEach((e, t) => {
          if ("%" == e.unit) {
            let r = t % 2 == 0 ? i : n;
            3 == t &&
              "circle" == this.name &&
              (r = Math.sqrt((i * i + n * n) / 2)),
              s.push((e.num * r) / 100);
          } else s.push(e.num * r.queryUnitSize(e.unit, !1));
        }),
        this.name)
      ) {
        case "polygon":
          if (s.length % 2 == 0) {
            let i = [];
            for (let n = 0; n < s.length; n += 2)
              i.push(new Dt(e + s[n], t + s[n + 1]));
            return new hs(i);
          }
          break;
        case "rectangle":
          if (4 == s.length)
            return Ko(e + s[0], t + s[1], e + s[0] + s[2], t + s[1] + s[3]);
          break;
        case "ellipse":
          if (4 == s.length) return $l(e + s[0], t + s[1], s[2], s[3]);
          break;
        case "circle":
          if (3 == s.length) return $l(e + s[0], t + s[1], s[2], s[2]);
      }
    }
    return null;
  }
};
function Vr(e, t, i, n, r, s) {
  if (e) {
    let o = new Yl();
    try {
      return e.visit(o), o.getShape(t, i, n, r, s);
    } catch (e) {
      V.warn(e, "toShape:");
    }
  }
  return Ko(t, i, t + n, i + r);
}
var ql = class extends ct {
  constructor(e) {
    super(), (this.reset = e), p(this, "counters", {}), p(this, "name", null);
  }
  visitIdent(e) {
    return (
      (this.name = e.toString()),
      this.reset
        ? (this.counters[this.name] = 0)
        : (this.counters[this.name] = (this.counters[this.name] || 0) + 1),
      e
    );
  }
  visitInt(e) {
    return (
      this.name && (this.counters[this.name] += e.num - (this.reset ? 0 : 1)), e
    );
  }
  visitSpaceList(e) {
    return this.visitValues(e.values), e;
  }
};
function fs(e, t) {
  let i = new ql(t);
  try {
    e.visit(i);
  } catch (e) {
    V.warn(e, "toCounters:");
  }
  return i.counters;
}
var Ir = class extends Jt {
  constructor(e, t) {
    super(), (this.baseUrl = e), (this.transformer = t);
  }
  visitURL(e) {
    return new Oe(this.transformer.transformURL(e.url, this.baseUrl));
  }
};
function Zl(e) {
  let t = {};
  return (
    Object.keys(e).forEach((i) => {
      t[i] = Array.from(e[i]);
    }),
    t
  );
}
function Zo(e, t) {
  if ("content" === t) {
    let t = e.cloneNode(!0);
    return (
      t.querySelectorAll("[data-adapt-pseudo]").forEach((e) => e.remove()),
      t.textContent || ""
    );
  }
  {
    let i = e.querySelector(`[data-adapt-pseudo="${t}"]`);
    return (i && i.textContent) || "";
  }
}
var nc = class {
    constructor(e, t) {
      (this.targetId = e),
        (this.resolved = t),
        p(this, "pageCounters", null),
        p(this, "spineIndex", -1),
        p(this, "pageIndex", -1);
    }
    equals(e) {
      return (
        this === e ||
        (!!e &&
          this.targetId === e.targetId &&
          this.resolved === e.resolved &&
          this.spineIndex === e.spineIndex &&
          this.pageIndex === e.pageIndex)
      );
    }
    isResolved() {
      return this.resolved;
    }
    resolve() {
      this.resolved = !0;
    }
    unresolve() {
      this.resolved = !1;
    }
  },
  sc = class {
    constructor(e, t) {
      (this.counterStore = e), (this.baseURL = t);
    }
    countersOfId(e, t) {
      (e = this.counterStore.documentURLTransformer.transformFragment(
        e,
        this.baseURL
      )),
        (this.counterStore.countersById[e] = t);
    }
    getExprContentListener() {
      return this.counterStore.getExprContentListener();
    }
  },
  oc = class {
    constructor(e, t, i, n) {
      (this.counterStore = e),
        (this.baseURL = t),
        (this.rootScope = i),
        (this.pageScope = n),
        p(this, "styler", null),
        p(this, "namedStringValues", {}),
        p(this, "runningElements", {});
    }
    setStyler(e) {
      this.styler = e;
    }
    getFragment(e) {
      let t = e.match(/^[^#]*#(.*)$/);
      return t ? t[1] : null;
    }
    getTransformedId(e) {
      let t = this.counterStore.documentURLTransformer.transformURL(
        J(e, this.baseURL),
        this.baseURL
      );
      return "#" === t.charAt(0) && (t = t.substring(1)), t;
    }
    getPageCounterVal(e, t) {
      let i = () => {
          let t = this.counterStore.currentPageCounters[e];
          return t && t.length ? t[t.length - 1] : null;
        },
        n = new Z(this.pageScope, () => t(i()), `page-counter-${e}`);
      return (
        this.counterStore.registerPageCounterExpr(
          e,
          (e) => t(e[e.length - 1]),
          n
        ),
        n
      );
    }
    getPageCountersVal(e, t) {
      let i = () => this.counterStore.currentPageCounters[e] || [],
        n = new Z(this.pageScope, () => t(i()), `page-counters-${e}`);
      return this.counterStore.registerPageCounterExpr(e, t, n), n;
    }
    getTargetCounters(e, t, i) {
      let n = this.counterStore.countersById[t];
      return (
        !n &&
          i &&
          e &&
          (this.styler.styleUntilIdIsReached(e),
          (n = this.counterStore.countersById[t])),
        n || null
      );
    }
    getTargetPageCounters(e) {
      return this.counterStore.currentPage.elementsById[e]
        ? this.counterStore.currentPageCounters
        : this.counterStore.pageCountersById[e] || null;
    }
    getTargetPageText(e, t) {
      if (this.counterStore.currentPage.elementsById[e]) {
        let i = this.counterStore.currentPage.elementsById[e];
        if (i && i.length > 0) {
          let e = i[0];
          return Zo(e, "before" === t || "after" === t ? t : "content");
        }
        return "";
      }
      if (e in this.counterStore.pageTextById) {
        let i = this.counterStore.pageTextById[e];
        return void 0 !== i[t] ? i[t] : i.content || "";
      }
      return null;
    }
    getTargetCounterVal(e, t, i) {
      let n = this.getFragment(e),
        r = this.getTransformedId(e),
        s = this.getTargetCounters(n, r, !1);
      if (s && s[t]) {
        let e = s[t];
        return new ie(this.rootScope, i(e[e.length - 1] || null));
      }
      let o = new Z(
        this.pageScope,
        () => {
          if (((s = this.getTargetCounters(n, r, !0)), s)) {
            if (s[t]) {
              let e = s[t];
              return i(e[e.length - 1] || null);
            }
            {
              let e = this.getTargetPageCounters(r);
              if (e) {
                if ((this.counterStore.resolveReference(r), e[t])) {
                  let n = e[t];
                  return i(n[n.length - 1] || null);
                }
                return i(0);
              }
              return this.counterStore.saveReferenceOfCurrentPage(r, !1), "??";
            }
          }
          return this.counterStore.saveReferenceOfCurrentPage(r, !1), "??";
        },
        `target-counter-${t}-of-${e}`
      );
      return this.counterStore.registerTargetCounterExpr(t, i, o, r), o;
    }
    getTargetCountersVal(e, t, i) {
      let n = this.getFragment(e),
        r = this.getTransformedId(e);
      return new Z(
        this.pageScope,
        () => {
          let e = this.getTargetPageCounters(r);
          if (e) {
            this.counterStore.resolveReference(r);
            let s = e[t] || [],
              o = this.getTargetCounters(n, r, !0)[t] || [];
            return i(s.concat(o));
          }
          return this.counterStore.saveReferenceOfCurrentPage(r, !1), "??";
        },
        `target-counters-${t}-of-${e}`
      );
    }
    getTargetTextVal(e, t) {
      let i = this.getTransformedId(e),
        n = new Z(
          this.pageScope,
          () => {
            if ("first-letter" === t) {
              let e = this.getTargetPageText(i, "before"),
                t = this.getTargetPageText(i, "content"),
                n = this.getTargetPageText(i, "after");
              if (null === e && null === t && null === n)
                return (
                  this.counterStore.saveReferenceOfCurrentPage(i, !1), "??"
                );
              let r =
                (null != e ? e : "") +
                (null != t ? t : "") +
                (null != n ? n : "");
              this.counterStore.resolveReference(i);
              let s = r.match(ls);
              return s ? s[0] : "";
            }
            let e = this.getTargetPageText(i, t);
            return null !== e
              ? (this.counterStore.resolveReference(i), e)
              : (this.counterStore.saveReferenceOfCurrentPage(i, !1), "??");
          },
          `target-text-${t}-of-${e}`
        );
      return this.counterStore.registerTargetTextExpr(t, n, i), n;
    }
    getNamedStringVal(e, t) {
      return new Z(
        this.pageScope,
        () => this.getRunningValue(this.namedStringValues, e, t),
        `named-string-${t}-${e}`
      );
    }
    getRunningElementVal(e, t) {
      return new Z(
        this.pageScope,
        () => this.getRunningValue(this.runningElements, e, t),
        `running-element-${t}-${e}`
      );
    }
    getRunningValue(e, t, i) {
      let n = e[t];
      if (!n) return "";
      let r = Object.keys(n)
          .map((e) => parseInt(e, 10))
          .sort(rr),
        s = this.counterStore.currentPage,
        o = s.isBlankPage ? s.offset - 1 : s.offset,
        a = s.isBlankPage
          ? o
          : Math.max(
              o,
              ...Array.from(s.container.querySelectorAll(`[${Ft}]`)).map((e) =>
                parseInt(e.getAttribute(Ft), 10)
              )
            ),
        l = -1,
        h = -1,
        u = -1,
        c = -1;
      for (let e = 0; e < r.length; e++) {
        let t = r[e],
          i = e > 0 ? r[e - 1] : -1,
          n = e < r.length - 1 ? r[e + 1] : -1;
        if (t > a) break;
        if (t >= o) {
          if ((l < 0 && ((l = t), (c = -1)), h < 0))
            if (t === o) h = t;
            else {
              i < l && (h = i);
              let e = s.container.querySelector(`[${Ft}="${t}"]`);
              if (e) {
                let i = s.container.querySelector(`[${Ft}="${o}"]`);
                if ((i || (i = s.container.querySelector(`[${Ft}="0"]`)), i))
                  for (let n = i; n; n = n.firstElementChild)
                    if (n === e) {
                      h = t;
                      break;
                    }
              } else h < 0 && (h = t);
            }
          u = t;
        } else (n > a || n < 0) && (l = h = u = c = t);
      }
      return n[{ first: l, start: h, last: u, "first-except": c }[i]] || "";
    }
    setNamedString(e, t, i) {
      (this.namedStringValues[e] || (this.namedStringValues[e] = {}))[i] = t;
    }
    setRunningElement(e, t) {
      (this.runningElements[e] || (this.runningElements[e] = {}))[t] =
        String(t);
    }
  },
  Fr = class {
    constructor(e) {
      (this.documentURLTransformer = e),
        p(this, "countersById", {}),
        p(this, "pageCountersById", {}),
        p(this, "pageTextById", {}),
        p(this, "currentPageCounters", {}),
        p(this, "previousPageCounters", {}),
        p(this, "currentPageCountersStack", []),
        p(this, "pageIndicesById", {}),
        p(this, "currentPage", null),
        p(this, "newReferencesOfCurrentPage", []),
        p(this, "referencesToSolve", []),
        p(this, "referencesToSolveStack", []),
        p(this, "unresolvedReferences", {}),
        p(this, "resolvedReferences", {}),
        p(this, "pagesCounterExprs", []),
        p(this, "pageCounterExprs", []),
        p(this, "targetCounterExprs", []),
        p(this, "targetTextExprs", []),
        (this.currentPageCounters.page = [0]);
    }
    createCounterListener(e) {
      return new sc(this, e);
    }
    createCounterResolver(e, t, i) {
      return new oc(this, e, t, i);
    }
    setCurrentPage(e) {
      this.currentPage = e;
    }
    definePageCounter(e, t) {
      this.currentPageCounters[e]
        ? this.currentPageCounters[e].push(t)
        : (this.currentPageCounters[e] = [t]);
    }
    forceSetPageCounter(e) {
      let t = this.currentPageCounters.page;
      t && t.length
        ? (t[t.length - 1] = e)
        : (this.currentPageCounters.page = [e]);
    }
    updatePageCounters(e, t) {
      this.previousPageCounters = Zl(this.currentPageCounters);
      let i,
        n = e["counter-reset"];
      if (n) {
        let e = n.evaluate(t);
        e && (i = fs(e, !0));
      }
      if (i) for (let e in i) this.definePageCounter(e, i[e]);
      let r,
        s = e["counter-increment"];
      if (s) {
        let e = s.evaluate(t);
        e && (r = fs(e, !1));
      }
      r ? "page" in r || (r.page = 1) : ((r = {}), (r.page = 1));
      for (let e in r) {
        this.currentPageCounters[e] || this.definePageCounter(e, 0);
        let t = this.currentPageCounters[e];
        t[t.length - 1] += r[e];
      }
    }
    pushPageCounters(e) {
      this.currentPageCountersStack.push(this.currentPageCounters),
        (this.currentPageCounters = Zl(e));
    }
    popPageCounters() {
      this.currentPageCounters = this.currentPageCountersStack.pop();
    }
    resolveReference(e) {
      let t = this.unresolvedReferences[e],
        i = this.resolvedReferences[e];
      i || (i = this.resolvedReferences[e] = []);
      let n = !1;
      for (let r = 0; r < this.referencesToSolve.length; ) {
        let s = this.referencesToSolve[r];
        if (s.targetId === e) {
          if ((s.resolve(), this.referencesToSolve.splice(r, 1), t)) {
            let e = t.indexOf(s);
            e >= 0 && t.splice(e, 1);
          }
          i.push(s), (n = !0);
        } else r++;
      }
      n || this.saveReferenceOfCurrentPage(e, !0);
    }
    saveReferenceOfCurrentPage(e, t) {
      if (!this.newReferencesOfCurrentPage.some((t) => t.targetId === e)) {
        let i = new nc(e, t);
        this.newReferencesOfCurrentPage.push(i);
      }
    }
    finishPage(e, t) {
      let i = Object.keys(this.currentPage.elementsById);
      if (i.length > 0) {
        let n = Zl(this.currentPageCounters);
        i.forEach((i) => {
          this.pageCountersById[i] = n;
          let r = this.currentPage.elementsById[i];
          if (r && r.length > 0) {
            let e = r[0];
            this.pageTextById[i] = {
              content: Zo(e, "content"),
              before: Zo(e, "before"),
              after: Zo(e, "after"),
            };
          }
          let s = this.pageIndicesById[i];
          if (s && s.pageIndex < t) {
            let e = this.resolvedReferences[i];
            if (e) {
              let t,
                n = this.unresolvedReferences[i];
              for (
                n || (n = this.unresolvedReferences[i] = []);
                (t = e.shift());

              )
                t.unresolve(), n.push(t);
            }
          }
          this.pageIndicesById[i] = { spineIndex: e, pageIndex: t };
        });
      }
      let n,
        r = this.previousPageCounters;
      for (; (n = this.newReferencesOfCurrentPage.shift()); ) {
        let i;
        (n.pageCounters = r),
          (n.spineIndex = e),
          (n.pageIndex = t),
          n.isResolved()
            ? ((i = this.resolvedReferences[n.targetId]),
              i || (i = this.resolvedReferences[n.targetId] = []))
            : ((i = this.unresolvedReferences[n.targetId]),
              i || (i = this.unresolvedReferences[n.targetId] = [])),
          i.every((e) => !n.equals(e)) && i.push(n);
      }
      this.currentPage = null;
    }
    getUnresolvedRefsToPage(e) {
      let t = [];
      Object.keys(e.elementsById).forEach((e) => {
        let i = this.unresolvedReferences[e];
        i && (t = t.concat(i));
      }),
        t.sort(
          (e, t) => e.spineIndex - t.spineIndex || e.pageIndex - t.pageIndex
        );
      let i = [],
        n = null;
      return (
        t.forEach((e) => {
          n && n.spineIndex === e.spineIndex && n.pageIndex === e.pageIndex
            ? n.refs.push(e)
            : ((n = {
                spineIndex: e.spineIndex,
                pageIndex: e.pageIndex,
                pageCounters: e.pageCounters,
                refs: [e],
              }),
              i.push(n));
        }),
        i
      );
    }
    pushReferencesToSolve(e) {
      this.referencesToSolveStack.push(this.referencesToSolve),
        (this.referencesToSolve = e);
    }
    popReferencesToSolve() {
      this.referencesToSolve = this.referencesToSolveStack.pop();
    }
    registerPageCounterExpr(e, t, i) {
      "pages" === e
        ? this.pagesCounterExprs.push({ expr: i, format: t })
        : this.pageCounterExprs.push({ expr: i, format: t });
    }
    registerTargetCounterExpr(e, t, i, n) {
      this.targetCounterExprs.push({
        name: e,
        expr: i,
        format: t,
        transformedId: n,
      });
    }
    registerTargetTextExpr(e, t, i) {
      this.targetTextExprs.push({
        pseudoElement: e,
        expr: t,
        transformedId: i,
      });
    }
    getExprContentListener() {
      return this.exprContentListener.bind(this);
    }
    exprContentListener(e, t, i) {
      if (e instanceof Z) {
        if ("viv-leader" == e.str) {
          let n = i.createElementNS("http://www.w3.org/1999/xhtml", "span");
          return (
            (n.textContent = t),
            n.setAttribute("data-viv-leader", e.key),
            n.setAttribute("data-viv-leader-value", t),
            n
          );
        }
        if (e.str.startsWith("running-element-")) {
          let e = t && i.querySelectorAll(`[${Ft}="${t}"]`);
          if (!e || 0 === e.length) return null;
          let n = e[e.length - 1].cloneNode(!0);
          return (
            this.fixPageCounterInRunningElement(n),
            (n.style.position = ""),
            (n.style.visibility = ""),
            n
          );
        }
        if (e.str.startsWith("target-counter-")) {
          let n = i.createElementNS("http://www.w3.org/1999/xhtml", "span");
          return (n.textContent = t), n.setAttribute(ec, e.key), n;
        }
        if (e.str.startsWith("target-text-")) {
          let n = i.createElementNS("http://www.w3.org/1999/xhtml", "span");
          return (n.textContent = t), n.setAttribute(tc, e.key), n;
        }
      }
      let n = this.pagesCounterExprs.findIndex((t) => t.expr === e) >= 0,
        r = !n && this.pageCounterExprs.findIndex((t) => t.expr === e) >= 0;
      if (n || r) {
        let r = i.createElementNS("http://www.w3.org/1999/xhtml", "span");
        return (r.textContent = t), r.setAttribute(n ? Ql : Jl, e.key), r;
      }
      return null;
    }
    fixPageCounterInRunningElement(e) {
      let t = e.querySelectorAll(`[${Jl}]`);
      for (let e of t) {
        let t = e.getAttribute(Jl),
          i = this.pageCounterExprs.find((e) => e.expr.key === t),
          n = (null == i ? void 0 : i.expr).str,
          r = null == n ? void 0 : n.replace(/^page-counters?-/, ""),
          s = this.currentPageCounters[r];
        s && (e.textContent = i.format(s));
      }
      let i = e.querySelectorAll(`[${ec}]`);
      for (let e of i) e.setAttribute(ph, !0);
      let n = e.querySelectorAll(`[${tc}]`);
      for (let e of n) e.setAttribute(hh, !0);
    }
    finishLastPage(e) {
      var t;
      let i = e.root.querySelectorAll(`[${Ql}]`),
        n = e.contentContainer.childElementCount;
      for (let e of i) {
        let t = e.getAttribute(Ql),
          i = this.pagesCounterExprs.findIndex((e) => e.expr.key === t);
        e.textContent = this.pagesCounterExprs[i].format([n]);
      }
      let r = e.root.querySelectorAll(`[${ph}]`);
      for (let e of r) {
        let t = e.getAttribute(ec),
          i = this.targetCounterExprs.find((e) => e.expr.key === t);
        if (i && i.transformedId) {
          let t = this.pageCountersById[i.transformedId];
          if (t) {
            let n = t[i.name];
            n && (e.textContent = i.format(n[n.length - 1]));
          }
        }
      }
      let s = e.root.querySelectorAll(`[${hh}]`);
      for (let e of s) {
        let i = e.getAttribute(tc),
          n = this.targetTextExprs.find((e) => e.expr.key === i);
        if (n && n.transformedId) {
          let i = this.pageTextById[n.transformedId];
          i && (e.textContent = null != (t = i[n.pseudoElement]) ? t : "");
        }
      }
    }
    createLayoutConstraint(e) {
      return new ic(this, e);
    }
  },
  Ql = "data-vivliostyle-pages-counter",
  Jl = "data-vivliostyle-page-counter",
  ec = "data-vivliostyle-target-counter",
  tc = "data-vivliostyle-target-text",
  ph = "data-vivliostyle-target-counter-in-running",
  hh = "data-vivliostyle-target-text-in-running",
  ic = class {
    constructor(e, t) {
      (this.counterStore = e), (this.pageIndex = t);
    }
    allowLayout(e) {
      if (!e || e.after) return !0;
      let t = e.viewNode;
      if (!t || 1 !== t.nodeType) return !0;
      let i =
        t.getAttribute("data-vivliostyle-id") ||
        t.getAttribute("id") ||
        t.getAttribute("name");
      if (
        !i ||
        (!this.counterStore.resolvedReferences[i] &&
          !this.counterStore.unresolvedReferences[i])
      )
        return !0;
      let n = this.counterStore.pageIndicesById[i];
      return !n || this.pageIndex >= n.pageIndex;
    }
  };
function Cm(e) {
  if ((e = e.substr(1)).match(/^[^0-9a-fA-F\n\r]$/)) return e;
  let t = parseInt(e, 16);
  return isNaN(t)
    ? ""
    : 0 === t || (t >= 55296 && t <= 57343) || t > 1114111
    ? "�"
    : String.fromCodePoint(t);
}
function gs(e) {
  return e.replace(
    /\\([0-9a-fA-F]{1,6}(\r\n|[ \n\r\t\f])?|[^0-9a-fA-F\n\r])/g,
    Cm
  );
}
var ac = class {
  constructor() {
    p(this, "type"),
      p(this, "precededBySpace", !1),
      p(this, "num", 0),
      p(this, "text", ""),
      p(this, "position", 0),
      (this.type = 0);
  }
  toString() {
    switch (this.type) {
      case 10:
        return "(";
      case 11:
        return ")";
      case 12:
        return "{";
      case 13:
        return "}";
      case 14:
        return "[";
      case 15:
        return "]";
      case 16:
        return ",";
      case 17:
        return ";";
      case 18:
        return ":";
      case 19:
        return "/";
      case 21:
        return "%";
      case 22:
        return "?";
      case 23:
        return "+";
      case 24:
        return "-";
      case 25:
        return "||";
      case 26:
        return "&&";
      case 31:
        return "!";
      case 32:
        return "$";
      case 33:
        return "^";
      case 34:
        return "|";
      case 35:
        return "~";
      case 36:
        return "*";
      case 37:
        return ">";
      case 38:
        return "<";
      case 39:
        return "=";
      case 41:
        return "!=";
      case 42:
        return "$=";
      case 43:
        return "^=";
      case 44:
        return "|=";
      case 45:
        return "~=";
      case 46:
        return "*=";
      case 47:
        return ">=";
      case 48:
        return "<=";
      case 49:
        return "==";
      case 50:
        return "::";
      case 51:
        return "\x3c!--";
      case 52:
        return "--\x3e";
      case 3:
        return this.num.toString() + this.text;
      case 4:
      case 5:
        return this.num.toString();
      case 20:
        return "@" + this.text;
      case 7:
        return "#" + this.text;
      case 6:
        return this.text + "(";
      case 9:
        return "." + this.text;
      case 0:
        return "/*EOF*/";
      default:
        return this.text;
    }
  }
};
function we(e, t) {
  let i,
    n = Array(128);
  for (i = 0; i < 128; i++) n[i] = e;
  for (n.NaN = 35 == e ? 35 : 72, i = 0; i < t.length; i += 2)
    n[t[i]] = t[i + 1];
  return n;
}
var fn = [
  72, 72, 72, 72, 72, 72, 72, 72, 72, 1, 1, 72, 1, 1, 72, 72, 72, 72, 72, 72,
  72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 1, 4, 34, 6, 7, 8, 9, 33, 10,
  11, 12, 13, 14, 15, 16, 17, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 18, 19, 20, 21, 22,
  23, 24, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3,
  3, 3, 3, 25, 29, 26, 30, 3, 72, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3,
  3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 27, 31, 28, 32, 72,
];
fn.NaN = 80;
var en = [
  43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43,
  43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43, 43,
  43, 43, 52, 43, 43, 43, 43, 39, 43, 43, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 43, 43, 43, 43, 43, 43, 43, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 43, 44, 43, 43,
  39, 43, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 39, 39, 39, 39, 39, 39, 39, 39, 43, 43, 43, 43, 43,
];
en.NaN = 43;
var bm = [
  72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72,
  72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72, 72,
  72, 72, 72, 72, 72, 72, 72, 78, 59, 72, 59, 59, 59, 59, 59, 59, 59, 59, 59,
  59, 72, 72, 72, 72, 72, 72, 72, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78,
  78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 72, 61, 72, 72,
  78, 72, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78,
  78, 78, 78, 78, 78, 78, 78, 78, 78, 72, 72, 72, 72, 72,
];
en.NaN = 43;
var gh = [
  35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35,
  35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35, 35,
  35, 35, 35, 35, 35, 35, 35, 57, 59, 35, 58, 58, 58, 58, 58, 58, 58, 58, 58,
  58, 35, 35, 35, 35, 35, 35, 35, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60,
  60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 35, 61, 35, 35,
  60, 35, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60, 60,
  60, 60, 60, 60, 60, 60, 60, 60, 60, 35, 35, 35, 35, 35,
];
gh.NaN = 35;
var mh = [
  45, 45, 45, 45, 45, 45, 45, 45, 45, 73, 45, 45, 45, 45, 45, 45, 45, 45, 45,
  45, 45, 45, 45, 45, 45, 45, 45, 45, 45, 45, 45, 45, 73, 45, 45, 45, 45, 45,
  45, 45, 53, 45, 45, 45, 45, 45, 45, 45, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 45, 45, 45, 45, 45, 45, 45, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 45, 44, 45, 45,
  39, 45, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 39, 39, 39, 39, 39, 39, 39, 39, 45, 45, 45, 45, 45,
];
mh.NaN = 45;
var lc = [
  37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37,
  37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 37, 41,
  37, 37, 37, 37, 37, 37, 37, 37, 42, 37, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 37, 37, 37, 37, 37, 37, 37, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40,
  40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 37, 37, 37, 37,
  40, 37, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40,
  40, 40, 40, 40, 40, 40, 40, 40, 40, 37, 37, 37, 37, 37,
];
lc.NaN = 37;
var cc = [
  38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38,
  38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 41,
  38, 38, 38, 38, 38, 38, 38, 38, 38, 38, 39, 39, 39, 39, 39, 39, 39, 39, 39,
  39, 38, 38, 38, 38, 38, 38, 38, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40,
  40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 38, 38, 38, 38,
  40, 38, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40, 40,
  40, 40, 40, 40, 40, 40, 40, 40, 40, 38, 38, 38, 38, 38,
];
cc.NaN = 38;
var ms = we(35, [61, 36]),
  xm = we(35, [58, 77]),
  ym = we(35, [61, 36, 124, 50]),
  Em = we(35, [38, 51]),
  Sm = we(35, [42, 54]),
  Nm = we(39, [42, 55]),
  vm = we(54, [42, 55, 47, 56]),
  Tm = we(62, [62, 82]),
  wm = we(35, [61, 36, 33, 70]),
  Pm = we(62, [45, 71]),
  km = we(63, [45, 81]),
  rc = we(76, [10, 72, 13, 72]),
  Am = we(39, [39, 46, 10, 72, 13, 72, 92, 48]),
  Lm = we(39, [34, 46, 10, 72, 13, 72, 92, 49]),
  Rm = we(39, [39, 47, 10, 74, 13, 74, 92, 48]),
  Im = we(39, [34, 47, 10, 74, 13, 74, 92, 49]),
  fh = we(64, [9, 39, 32, 39, 34, 66, 39, 65, 41, 72, 10, 39, 13, 39]),
  Vm = we(39, [
    41,
    67,
    9,
    79,
    10,
    79,
    13,
    79,
    32,
    79,
    92,
    75,
    40,
    72,
    91,
    72,
    93,
    72,
    123,
    72,
    125,
    72,
    NaN,
    67,
  ]),
  Fm = we(39, [39, 68, 10, 74, 13, 74, 92, 75, NaN, 67]),
  Bm = we(39, [34, 68, 10, 74, 13, 74, 92, 75, NaN, 67]),
  Om = we(72, [9, 39, 10, 39, 13, 39, 32, 39, 41, 69]),
  Dm = 15,
  De = class {
    constructor(e, t) {
      (this.input = e),
        (this.handler = t),
        p(this, "indexMask"),
        p(this, "buffer"),
        p(this, "tail", 0),
        p(this, "curr", 0),
        p(this, "position", 0),
        (this.indexMask = Dm),
        (this.buffer = Array(this.indexMask + 1));
      for (let e = 0; e <= this.indexMask; e++) this.buffer[e] = new ac();
    }
    token() {
      return (
        this.tail == this.curr && this.fillBuffer(), this.buffer[this.curr]
      );
    }
    nthToken(e) {
      return (
        ((this.tail - this.curr) & this.indexMask) <= e && this.fillBuffer(),
        this.buffer[(this.curr + e) & this.indexMask]
      );
    }
    consume() {
      this.curr = (this.curr + 1) & this.indexMask;
    }
    fillBuffer() {
      let e = this.tail,
        t = this.curr,
        i = this.indexMask;
      if ((e >= t ? (t += i) : t--, t == e))
        throw new Error("F_CSSTOK_INTERNAL");
      let n = fn,
        r = this.input,
        s = this.position,
        o = this.buffer,
        a = 0,
        l = 0,
        h = "",
        u = 0,
        c = !1,
        d = o[e],
        p = -9;
      for (;;) {
        let f = r.charCodeAt(s);
        switch (n[f] || n[65]) {
          case 72:
            (h = r.substring(l, s)), (a = isNaN(f) ? 0 : 54), (n = fn);
            break;
          case 1:
            s++, (c = !0);
            continue;
          case 2:
            (l = s++), (n = lc);
            continue;
          case 3:
            (a = 1), (l = s++), (n = en);
            continue;
          case 4:
            (l = s++), (a = 31), (n = ms);
            continue;
          case 33:
            (a = 2), (l = ++s), (n = Am);
            continue;
          case 34:
            (a = 2), (l = ++s), (n = Lm);
            continue;
          case 6:
            (l = ++s), (a = 7), (n = en);
            continue;
          case 7:
            (l = s++), (a = 32), (n = ms);
            continue;
          case 8:
            (l = s++), (a = 21);
            break;
          case 9:
            (l = s++), (a = 32), (n = Em);
            continue;
          case 10:
            (l = s++), (a = 10);
            break;
          case 11:
            (l = s++), (a = 11);
            break;
          case 12:
            (l = s++), (a = 36), (n = ms);
            continue;
          case 13:
            (l = s++), (a = 23);
            break;
          case 14:
            (l = s++), (a = 16);
            break;
          case 15:
            (a = 24), (l = s++), (n = gh);
            continue;
          case 16:
            (l = s++), (n = bm);
            continue;
          case 78:
            (l = s++), (a = 9), (n = en);
            continue;
          case 17:
            (l = s++), (a = 19), (n = Sm);
            continue;
          case 18:
            (l = s++), (a = 18), (n = xm);
            continue;
          case 77:
            s++, (a = 50);
            break;
          case 19:
            (l = s++), (a = 17);
            break;
          case 20:
            (l = s++), (a = 38), (n = wm);
            continue;
          case 21:
            (l = s++), (a = 39), (n = ms);
            continue;
          case 22:
            (l = s++), (a = 37), (n = ms);
            continue;
          case 23:
            (l = s++), (a = 22);
            break;
          case 24:
            (l = ++s), (a = 20), (n = en);
            continue;
          case 25:
            (l = s++), (a = 14);
            break;
          case 26:
            (l = s++), (a = 15);
            break;
          case 27:
            (l = s++), (a = 12);
            break;
          case 28:
            (l = s++), (a = 13);
            break;
          case 29:
            (l = s++), (p = l), (a = 1), (n = rc);
            continue;
          case 30:
            (l = s++), (a = 33), (n = ms);
            continue;
          case 31:
            (l = s++), (a = 34), (n = ym);
            continue;
          case 32:
            (l = s++), (a = 35), (n = ms);
            continue;
          case 35:
            break;
          case 36:
            s++, (a = a + 41 - 31);
            break;
          case 37:
            (a = 5), (u = parseInt(r.substring(l, s), 10));
            break;
          case 38:
            (a = 4), (u = parseFloat(r.substring(l, s)));
            break;
          case 39:
            s++;
            continue;
          case 40:
            (a = 3), (u = parseFloat(r.substring(l, s))), (l = s++), (n = en);
            continue;
          case 41:
            (a = 3), (u = parseFloat(r.substring(l, s))), (h = "%"), (l = s++);
            break;
          case 42:
            s++, (n = cc);
            continue;
          case 43:
            if (
              ((h = gs(r.substring(l, s))),
              (27 === a && 63 === f) ||
                (1 === a &&
                  "u" === h.toLowerCase() &&
                  /^(\bu\+[?0-9a-f]+(-[?0-9a-f]+)?|,|\s+|\/\*([^*]|\*[^/])*\*\/)+[;}]/i.test(
                    r.substring(s - 1)
                  )))
            ) {
              (a = 27), s++;
              continue;
            }
            break;
          case 44:
            (p = s++), (n = rc);
            continue;
          case 45:
            h = gs(r.substring(l, s));
            break;
          case 46:
            (h = r.substring(l, s)), s++;
            break;
          case 47:
            (h = gs(r.substring(l, s))), s++;
            break;
          case 48:
            (p = s), (s += 2), (n = Rm);
            continue;
          case 49:
            (p = s), (s += 2), (n = Im);
            continue;
          case 50:
            s++, (a = 25);
            break;
          case 51:
            s++, (a = 26);
            break;
          case 52:
            if (((h = r.substring(l, s)), 1 == a)) {
              if ((s++, "url" == h.toLowerCase())) {
                n = fh;
                continue;
              }
              a = 6;
            }
            break;
          case 53:
            if (((h = gs(r.substring(l, s))), 1 == a)) {
              if ((s++, "url" == h.toLowerCase())) {
                n = fh;
                continue;
              }
              a = 6;
            }
            break;
          case 54:
            (n = Nm), s++;
            continue;
          case 55:
            (n = vm), s++;
            continue;
          case 56:
            (n = fn), s++;
            continue;
          case 57:
            (n = Tm), s++, ">" !== r[s] && ((a = 1), (n = en));
            continue;
          case 81:
            (a = 51), (h = r.substring(l, ++s)), (n = fn);
            break;
          case 82:
            (a = 52), (h = r.substring(l, ++s)), (n = fn);
            break;
          case 58:
            (a = 5), (n = lc), s++;
            continue;
          case 59:
            (a = 4), (n = cc), s++;
            continue;
          case 60:
            (a = 1), (n = en), s++;
            continue;
          case 61:
            (a = 1), (n = rc), (p = s++);
            continue;
          case 62:
            s--;
            break;
          case 63:
            s -= 2;
            break;
          case 64:
            (l = s++), (n = Vm);
            continue;
          case 65:
            (l = ++s), (n = Fm);
            continue;
          case 66:
            (l = ++s), (n = Bm);
            continue;
          case 67:
            (a = 8), (h = gs(r.substring(l, s))), s++;
            break;
          case 69:
            s++;
            break;
          case 70:
            (n = Pm), s++;
            continue;
          case 71:
            (n = km), s++;
            continue;
          case 79:
            if (
              s - p < 8 &&
              r
                .substring(p + 1, s + 1)
                .match(/^[0-9a-fA-F]{0,6}(\r\n|[\n\r])|[ \t]$/)
            ) {
              s++;
              continue;
            }
          case 68:
            (a = 8), (h = gs(r.substring(l, s))), s++, (n = Om);
            continue;
          case 74:
            if (
              (s++,
              s - p < 9 &&
                r.substring(p + 1, s).match(/^[0-9a-fA-F]{0,6}(\r\n|[\n\r])$/))
            )
              continue;
            (a = 54), (h = "E_CSS_UNEXPECTED_NEWLINE"), (n = fn);
            break;
          case 73:
            if (
              s - p < 9 &&
              r.substring(p + 1, s + 1).match(/^[0-9a-fA-F]{0,6}[ \t]$/)
            ) {
              s++, (n = en);
              continue;
            }
            h = gs(r.substring(l, s));
            break;
          case 75:
            p = s++;
            continue;
          case 76:
            s++, (n = mh);
            continue;
          default:
            if (n !== fn) {
              (a = 54), (h = "E_CSS_UNEXPECTED_STATE");
              break;
            }
            (l = s), (a = 0);
        }
        if (
          ((d.type = a),
          (d.precededBySpace = c),
          (d.num = u),
          (d.text = h),
          (d.position = l),
          e++,
          e >= t)
        )
          break;
        (n = fn), (c = !1), (d = o[e & i]);
      }
      (this.position = s), (this.tail = e & i);
    }
  },
  Ne = null,
  uc = null;
function Jo() {
  return Ne;
}
function A(e) {
  if (!Ne) throw new Error("E_TASK_NO_CONTEXT");
  Ne.name || (Ne.name = e);
  let t = Ne,
    i = new ao(t, t.top, e);
  return (t.top = i), (i.state = 1), i;
}
function Mm(e) {
  return new pc(e || new dc());
}
function T(e) {
  return new Qo(e);
}
function gn(e, t, i) {
  let n = A(e);
  n.handler = i;
  try {
    t(n);
  } catch (e) {
    n.task.raise(e, n);
  }
  return n.result();
}
function Ch(e, t) {
  return (Ne ? Ne.getScheduler() : uc || Mm()).run(e, t);
}
var dc = class {
    currentTime() {
      return new Date().valueOf();
    }
    setTimeout(e, t) {
      return setTimeout(e, t);
    }
    clearTimeout(e) {
      clearTimeout(e);
    }
  },
  pc = class {
    constructor(e) {
      (this.timer = e),
        p(this, "timeout", 1),
        p(this, "slice", 25),
        p(this, "sliceOverTime", 0),
        p(this, "queue"),
        p(this, "wakeupTime", null),
        p(this, "timeoutToken", null),
        p(this, "inTimeSlice", !1),
        p(this, "order", 0),
        (this.queue = new ir()),
        uc || (uc = this);
    }
    setSlice(e) {
      this.slice = e;
    }
    setTimeout(e) {
      this.timeout = e;
    }
    isTimeSliceOver() {
      return this.timer.currentTime() >= this.sliceOverTime;
    }
    arm() {
      if (this.inTimeSlice) return;
      let e = this.queue.peek().scheduledTime,
        t = this.timer.currentTime();
      if (null != this.timeoutToken) {
        if (t + this.timeout > this.wakeupTime) return;
        this.timer.clearTimeout(this.timeoutToken);
      }
      let i = e - t;
      i <= this.timeout && (i = this.timeout),
        (this.wakeupTime = t + i),
        (this.timeoutToken = this.timer.setTimeout(() => {
          (this.timeoutToken = null), this.doTimeSlice();
        }, i));
    }
    schedule(e, t) {
      let i = e,
        n = this.timer.currentTime();
      (i.order = this.order++),
        (i.scheduledTime = n + (t || 0)),
        this.queue.add(i),
        this.arm();
    }
    doTimeSlice() {
      null != this.timeoutToken &&
        (this.timer.clearTimeout(this.timeoutToken),
        (this.timeoutToken = null)),
        (this.inTimeSlice = !0);
      try {
        let e = this.timer.currentTime();
        for (this.sliceOverTime = e + this.slice; this.queue.length(); ) {
          let t = this.queue.peek();
          if (
            t.scheduledTime > e ||
            (this.queue.remove(),
            t.canceled || t.resumeInternal(),
            (e = this.timer.currentTime()),
            e >= this.sliceOverTime)
          )
            break;
        }
      } catch (e) {
        V.error(e);
      }
      (this.inTimeSlice = !1), this.queue.length() && this.arm();
    }
    run(e, t) {
      let i = new hc(this, t || "");
      (i.top = new ao(i, null, "bootstrap")),
        (i.top.state = 1),
        i.top.then(() => {
          let t = () => {
            i.running = !1;
            for (let e of i.callbacks)
              try {
                e();
              } catch (e) {
                V.error(e);
              }
          };
          try {
            e().then((e) => {
              (i.result = e), t();
            });
          } catch (e) {
            i.raise(e), t();
          }
        });
      let n = Ne;
      return (Ne = i), this.schedule(i.top.suspend("bootstrap")), (Ne = n), i;
    }
  },
  Br = class {
    constructor(e) {
      (this.task = e),
        p(this, "scheduledTime", 0),
        p(this, "order", 0),
        p(this, "result", null),
        p(this, "canceled", !1);
    }
    compare(e) {
      let t = e;
      return t.scheduledTime - this.scheduledTime || t.order - this.order;
    }
    getTask() {
      return this.task;
    }
    schedule(e, t) {
      (this.result = e), this.task.scheduler.schedule(this, t);
    }
    resumeInternal() {
      let e = this.task;
      if (((this.task = null), e && e.continuation == this)) {
        e.continuation = null;
        let t = Ne;
        return (Ne = e), e.top.finish(this.result), (Ne = t), !0;
      }
      return !1;
    }
    cancel() {
      this.canceled = !0;
    }
  },
  hc = class {
    constructor(e, t) {
      (this.scheduler = e),
        (this.name = t),
        p(this, "callbacks", []),
        p(this, "exception", null),
        p(this, "running", !0),
        p(this, "result", null),
        p(this, "waitTarget", null),
        p(this, "top", null),
        p(this, "continuation", null);
    }
    getName() {
      return this.name;
    }
    interrupt(e) {
      if (
        (this.raise(e || new Error("E_TASK_INTERRUPT")),
        this !== Ne && this.continuation)
      ) {
        this.continuation.cancel();
        let e = new Br(this);
        (this.waitTarget = "interrupt"),
          (this.continuation = e),
          this.scheduler.schedule(e);
      }
    }
    getScheduler() {
      return this.scheduler;
    }
    isRunning() {
      return this.running;
    }
    whenDone(e) {
      this.callbacks.push(e);
    }
    join() {
      let e = A("Task.join");
      if (this.running) {
        let t = e.suspend(this);
        this.whenDone(() => {
          t.schedule(this.result);
        });
      } else e.finish(this.result);
      return e.result();
    }
    unwind() {
      for (; this.top && !this.top.handler; ) this.top = this.top.parent;
      if (this.top && this.top.handler && this.exception) {
        let e = this.exception;
        (this.exception = null), this.top.handler(this.top, e);
      } else
        this.exception &&
          V.error(this.exception, "Unhandled exception in task", this.name);
    }
    raise(e, t) {
      if ((this.fillStack(e), t)) {
        let e = this.top;
        for (; e && e != t; ) e = e.parent;
        e == t && (this.top = e);
      }
      (this.exception = e), this.unwind();
    }
    fillStack(e) {
      let t = e.frameTrace;
      if (!t) {
        t = e.stack ? `${e.stack}\n\t---- async ---\n` : "";
        for (let e = this.top; e; e = e.parent)
          (t += "\t"), (t += e.getName()), (t += "\n");
        e.frameTrace = t;
      }
    }
  },
  Qo = class e {
    constructor(e) {
      this.value = e;
    }
    then(e) {
      e(this.value);
    }
    thenAsync(e) {
      return e(this.value);
    }
    thenReturn(t) {
      return new e(t);
    }
    thenFinish(e) {
      e.finish(this.value);
    }
    isPending() {
      return !1;
    }
    get() {
      return this.value;
    }
  },
  fc = class {
    constructor(e) {
      this.frame = e;
    }
    then(e) {
      this.frame.then(e);
    }
    thenAsync(e) {
      if (this.isPending()) {
        let t = new ao(
          this.frame.task,
          this.frame.parent,
          "AsyncResult.thenAsync"
        );
        return (
          (t.state = 1),
          (this.frame.parent = t),
          this.frame.then((i) => {
            e(i).then((e) => {
              t.finish(e);
            });
          }),
          t.result()
        );
      }
      return e(this.frame.res);
    }
    thenReturn(e) {
      return this.isPending() ? this.thenAsync(() => new Qo(e)) : new Qo(e);
    }
    thenFinish(e) {
      this.isPending()
        ? this.then((t) => {
            e.finish(t);
          })
        : e.finish(this.frame.res);
    }
    isPending() {
      return 1 == this.frame.state;
    }
    get() {
      if (this.isPending()) throw new Error("Result is pending");
      return this.frame.res;
    }
  },
  ao = class {
    constructor(e, t, i) {
      (this.task = e),
        (this.parent = t),
        (this.name = i),
        p(this, "res", null),
        p(this, "state"),
        p(this, "callback", null),
        p(this, "handler", null),
        (this.state = 0);
    }
    checkEnvironment() {
      if (!Ne) throw new Error("F_TASK_NO_CONTEXT");
      if (this !== Ne.top) throw new Error("F_TASK_NOT_TOP_FRAME");
    }
    result() {
      return new fc(this);
    }
    finish(e) {
      this.checkEnvironment(),
        Ne && !Ne.exception && (this.res = e),
        (this.state = 2);
      let t = this.parent;
      if ((Ne && (Ne.top = t), this.callback)) {
        try {
          this.callback(e);
        } catch (e) {
          this.task.raise(e, t);
        }
        this.state = 3;
      }
    }
    getTask() {
      return this.task;
    }
    getName() {
      return this.name;
    }
    getScheduler() {
      return this.task.scheduler;
    }
    then(e) {
      switch (this.state) {
        case 1:
          if (this.callback)
            throw new Error("F_TASK_FRAME_ALREADY_HAS_CALLBACK");
          this.callback = e;
          break;
        case 2: {
          let t = this.task,
            i = this.parent;
          try {
            e(this.res), (this.state = 3);
          } catch (e) {
            (this.state = 3), t.raise(e, i);
          }
          break;
        }
        case 3:
          throw new Error("F_TASK_DEAD_FRAME");
        default:
          throw new Error(`F_TASK_UNEXPECTED_FRAME_STATE ${this.state}`);
      }
    }
    timeSlice() {
      let e = A("Frame.timeSlice");
      return (
        e.getScheduler().isTimeSliceOver()
          ? (V.debug("-- time slice --"), e.suspend().schedule(!0))
          : e.finish(!0),
        e.result()
      );
    }
    sleep(e) {
      let t = A("Frame.sleep");
      return t.suspend().schedule(!0, e), t.result();
    }
    loop(e) {
      let t = A("Frame.loop"),
        i = (n) => {
          try {
            for (; n; ) {
              let t = e();
              if (t.isPending()) return void t.then(i);
              t.then((e) => {
                n = e;
              });
            }
            t.finish(!0);
          } catch (e) {
            t.task.raise(e, t);
          }
        };
      return i(!0), t.result();
    }
    loopWithFrame(e) {
      let t = Ne;
      if (!t) throw new Error("E_TASK_NO_CONTEXT");
      return this.loop(() => {
        let i;
        do {
          let n = new gc(t, t.top);
          (t.top = n), (n.state = 1), e(n), (i = n.result());
        } while (!i.isPending() && i.get());
        return i;
      });
    }
    suspend(e) {
      if ((this.checkEnvironment(), this.task.continuation))
        throw new Error("E_TASK_ALREADY_SUSPENDED");
      let t = new Br(this.task);
      return (
        (this.task.continuation = t),
        (Ne = null),
        (this.task.waitTarget = e || null),
        t
      );
    }
  },
  gc = class extends ao {
    constructor(e, t) {
      super(e, t, "loop");
    }
    continueLoop() {
      this.finish(!0);
    }
    breakLoop() {
      this.finish(!1);
    }
  },
  tn = class {
    constructor(e, t) {
      (this.fetch = e),
        p(this, "name"),
        p(this, "arrived", !1),
        p(this, "resource", null),
        p(this, "task", null),
        p(this, "piggybacks", []),
        (this.name = t);
    }
    start() {
      this.task ||
        (this.task = Jo()
          .getScheduler()
          .run(() => {
            let e = A("Fetcher.run");
            return (
              this.fetch().then((t) => {
                let i = this.piggybacks;
                if (
                  ((this.arrived = !0),
                  (this.resource = t),
                  (this.task = null),
                  (this.piggybacks = []),
                  i)
                )
                  for (let e = 0; e < i.length; e++)
                    try {
                      i[e](t);
                    } catch (e) {
                      V.error(e, "Error:");
                    }
                e.finish(t);
              }),
              e.result()
            );
          }, this.name));
    }
    piggyback(e) {
      this.arrived ? e(this.resource) : this.piggybacks.push(e);
    }
    get() {
      return this.arrived ? T(this.resource) : (this.start(), this.task.join());
    }
    hasArrived() {
      return this.arrived;
    }
  },
  Bn = (e) => {
    if (0 == e.length) return T(!0);
    if (1 == e.length) return e[0].get().thenReturn(!0);
    let t = A("waitForFetches"),
      i = 0;
    return (
      t
        .loop(() => {
          for (; i < e.length; ) {
            let t = e[i++];
            if (!t.hasArrived()) return t.get().thenReturn(!0);
          }
          return T(!1);
        })
        .then(() => {
          t.finish(!0);
        }),
      t.result()
    );
  },
  mc =
    '\n@media screen {\n  [data-vivliostyle-viewer-viewport] {\n    background: #aaaaaa;\n  }\n\n  [data-vivliostyle-page-container] {\n    background: white;\n    z-index: 0;\n  }\n\n  [data-vivliostyle-viewer-viewport] {\n    box-sizing: border-box;\n    display: flex;\n    overflow: auto;\n    position: relative;\n  }\n\n  [data-vivliostyle-outer-zoom-box] {\n    margin: auto;\n    overflow: hidden;\n    flex: none;\n  }\n\n  [data-vivliostyle-viewer-viewport] [data-vivliostyle-spread-container] {\n    display: flex;\n    flex: none;\n    justify-content: center;\n    transform-origin: left top;\n  }\n\n  [data-vivliostyle-viewer-viewport][data-vivliostyle-page-progression="ltr"]\n    [data-vivliostyle-spread-container] {\n    flex-direction: row;\n  }\n\n  [data-vivliostyle-viewer-viewport][data-vivliostyle-page-progression="rtl"]\n    [data-vivliostyle-spread-container] {\n    flex-direction: row-reverse;\n  }\n\n  [data-vivliostyle-viewer-viewport] [data-vivliostyle-page-container] {\n    margin: 0 auto;\n    flex: none;\n    transform-origin: center top;\n  }\n\n  [data-vivliostyle-viewer-viewport][data-vivliostyle-spread-view="true"]\n    [data-vivliostyle-spread-container]\n    [data-vivliostyle-page-container][data-vivliostyle-page-side="left"] {\n    margin-right: 1px;\n    transform-origin: right top;\n  }\n\n  [data-vivliostyle-viewer-viewport][data-vivliostyle-spread-view="true"]\n    [data-vivliostyle-spread-container]\n    [data-vivliostyle-page-container][data-vivliostyle-page-side="right"] {\n    margin-left: 1px;\n    transform-origin: left top;\n  }\n\n  [data-vivliostyle-viewer-viewport][data-vivliostyle-spread-view="true"]\n    [data-vivliostyle-spread-container]\n    [data-vivliostyle-page-container][data-vivliostyle-unpaired-page="true"] {\n    margin-left: auto;\n    margin-right: auto;\n    transform-origin: center top;\n  }\n}\n',
  Cc =
    "\n[data-vivliostyle-layout-box] {\n  position: absolute;\n  left: 0;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  overflow: hidden;\n  z-index: -1;\n  transform-origin: left top;\n}\n\n[data-vivliostyle-debug] [data-vivliostyle-layout-box] {\n  right: auto;\n  bottom: auto;\n  overflow: visible;\n  z-index: auto;\n}\n\n[data-vivliostyle-spread-container] {\n  transform: scale(var(--viv-outputScale,1));\n  transform-origin: left top;\n}\n\n/* Emulate high pixel ratio using zoom & transform:scale() */\n@supports (zoom: 8) {\n  [data-vivliostyle-layout-box] {\n    zoom: calc(var(--viv-outputPixelRatio,1) / var(--viv-devicePixelRatio,1));\n    transform: scale(calc(var(--viv-devicePixelRatio,1) / var(--viv-outputPixelRatio,1)));\n  }\n  [data-vivliostyle-spread-container] {\n    zoom: calc(var(--viv-outputPixelRatio,1) / var(--viv-devicePixelRatio,1));\n    transform: scale(calc(var(--viv-outputScale,1) * var(--viv-devicePixelRatio,1) / var(--viv-outputPixelRatio,1)));\n  }\n  /* Workaround for Chromium's default border etc. widths not zoomed but scaled down */\n  [data-vivliostyle-spread-container] :where([style*=border],[style*=outline],[style*=rule]) {\n    border-width: medium;\n    outline-width: medium;\n    column-rule-width: medium;\n  }\n  [data-vivliostyle-spread-container] ::-webkit-scrollbar {\n    width: 8px;\n    height: 8px;\n  }\n  [data-vivliostyle-spread-container] ::-webkit-scrollbar-track {\n    background-color: #f4f4f4;\n  }\n  [data-vivliostyle-spread-container] ::-webkit-scrollbar-thumb {\n    border-radius: 4px;\n    background: #c7c7c7;\n  }\n  [data-vivliostyle-spread-container] ::-webkit-scrollbar-thumb:hover {\n    background: #7d7d7d;\n  }\n}\n\n[data-vivliostyle-page-container] {\n  position: relative;\n}\n\n[data-vivliostyle-bleed-box] {\n  position: relative;\n  overflow: hidden;\n  background-origin: content-box !important;\n}\n\n[data-vivliostyle-page-box] ~ [data-vivliostyle-page-box] {\n  display: none;\n}\n\n[data-vivliostyle-toc-box] {\n  position: absolute;\n  left: 3px;\n  top: 3px;\n  overflow: scroll;\n  overflow-x: hidden;\n  background: rgba(248, 248, 248, 0.9);\n  border-radius: 2px;\n  box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);\n}\n\n@media print {\n  [data-vivliostyle-toc-box] {\n    display: none;\n  }\n\n  [data-vivliostyle-outer-zoom-box],\n  [data-vivliostyle-spread-container] {\n    width: 100% !important;\n    height: 100% !important;\n  }\n\n  [data-vivliostyle-spread-container] {\n    --viv-outputScale: 1 !important;\n    --viv-devicePixelRatio: 1 !important;\n    zoom: normal !important;\n    transform: none !important;\n    print-color-adjust: exact;\n  }\n\n  @supports (zoom: 8) {\n    [data-vivliostyle-spread-container] [data-vivliostyle-page-container] {\n      zoom: var(--viv-outputPixelRatio,1);\n      /* transform: scale(calc(1 / var(--viv-outputPixelRatio,1))); */\n      /* Use matrix instead of scale (Workaround for issue #1555) */\n      transform: matrix(calc(1 / var(--viv-outputPixelRatio,1)), 0, 5e-324, calc(1 / var(--viv-outputPixelRatio,1)), 0, 0);\n      transform-origin: left top;\n    }\n  }\n\n  [data-vivliostyle-spread-container] [data-vivliostyle-page-container] {\n    display: block !important;\n    max-height: 100vh;\n  }\n\n  [data-vivliostyle-spread-container] [data-vivliostyle-page-container]:not(:last-child) {\n    break-after: page;\n  }\n\n  /* Gecko-only hack, see https://bugzilla.mozilla.org/show_bug.cgi?id=267029#c17 */\n  @-moz-document url-prefix()  {\n    [data-vivliostyle-spread-container] [data-vivliostyle-page-container]:nth-last-child(n + 2) {\n      top: -1px;\n      margin-top: 1px;\n      margin-bottom: -1px;\n    }\n    /* Workaround Gecko problem on page break */\n    [data-vivliostyle-spread-container] [data-vivliostyle-page-container] {\n      break-after: auto !important;\n      height: 100% !important;\n    }\n  }\n}\n",
  bc =
    "\n/*\n * Copyright 2013 Google, Inc.\n * Copyright 2015 Daishinsha Inc.\n * Copyright 2019 Vivliostyle Foundation\n *\n * CSS property validation.\n */\nNUM = POS_NUM | ZERO | NEGATIVE;\nNNEG_NUM = POS_NUM | ZERO;\nINT = POS_INT | ZERO | NEGATIVE;\nNNEG_INT = POS_INT | ZERO;\nPERCENTAGE = POS_PERCENTAGE | ZERO | NEGATIVE;\nSTRICT_PERCENTAGE = POS_PERCENTAGE | ZERO_PERCENTAGE | NEGATIVE;\nNNEG_PERCENTAGE = POS_PERCENTAGE | ZERO;\nLENGTH = POS_LENGTH | ZERO | NEGATIVE;\nNNEG_LENGTH = POS_LENGTH | ZERO;\nPLENGTH = LENGTH | PERCENTAGE;\nPPLENGTH = POS_LENGTH | ZERO | POS_PERCENTAGE;\nALENGTH = LENGTH | auto;\nAPLENGTH = PLENGTH | auto;\nPAPLENGTH = PPLENGTH | auto;\nANGLE = POS_ANGLE | ZERO | NEGATIVE;\nLENGTH_OR_NUM = LENGTH | NUM;\nANGLE_OR_NUM = ANGLE | NUM;\nMIN_MAX_FIT_CONTENT = min-content | max-content | fit-content;\nBG_POSITION_TERM = PLENGTH | left | center | right | top | bottom;\nURI_OR_NONE = URI | none;\nIMAGE = URI | IMAGE_FUNCTION | none;\nbackground-attachment = COMMA( [scroll | fixed | local]+ );\nbackground-color = COLOR;\nbackground-image = COMMA( IMAGE+ );\nbackground-position = COMMA( SPACE(BG_POSITION_TERM{1,4})+ ); /* relaxed */\nbackground-repeat = COMMA( [repeat | repeat-x | repeat-y | no-repeat]+ );\nborder-collapse = collapse | separate;\nBORDER_SIDE_COLOR = COLOR;\nBORDER_SIDE_STYLE = none | hidden | dotted | dashed | solid | double | groove | ridge | inset | outset;\nBORDER_SIDE_WIDTH = thin: 1px | medium: 3px | thick: 5px | NNEG_LENGTH;\nborder-spacing = LENGTH LENGTH?;\nborder-top-color = BORDER_SIDE_COLOR;\nborder-right-color = BORDER_SIDE_COLOR;\nborder-bottom-color = BORDER_SIDE_COLOR;\nborder-left-color = BORDER_SIDE_COLOR;\nborder-top-style = BORDER_SIDE_STYLE;\nborder-right-style = BORDER_SIDE_STYLE;\nborder-bottom-style = BORDER_SIDE_STYLE;\nborder-left-style = BORDER_SIDE_STYLE;\nborder-top-width = BORDER_SIDE_WIDTH;\nborder-right-width = BORDER_SIDE_WIDTH;\nborder-bottom-width = BORDER_SIDE_WIDTH;\nborder-left-width = BORDER_SIDE_WIDTH;\nBORDER_RADIUS = PLENGTH{1,2};\nborder-top-left-radius = BORDER_RADIUS;\nborder-top-right-radius = BORDER_RADIUS;\nborder-bottom-right-radius = BORDER_RADIUS;\nborder-bottom-left-radius = BORDER_RADIUS;\nborder-image-source = IMAGE;\nborder-image-slice = [NUM | PERCENTAGE]{1,4} || fill; /* relaxed */\nborder-image-width = [NUM | PLENGTH | auto]{1,4};\nborder-image-outset = [NUM | LENGTH]{1,4};\nborder-image-repeat = [ stretch | repeat | round | space ]{1,2};\nbottom = APLENGTH;\ncaption-side = top | bottom;\nclip = rect(ALENGTH{4}) | rect(SPACE(ALENGTH{4})) | auto;\ncolor = COLOR;\nLIST_STYLE_TYPE = IDENT;\nTYPE_OR_UNIT_IN_ATTR = string | color | url | integer | number | length | angle | time | frequency;\nATTR = attr(SPACE(IDENT TYPE_OR_UNIT_IN_ATTR?) [ STRING | IDENT | COLOR | INT | NUM | PLENGTH | ANGLE | POS_TIME | FREQUENCY]?);\nCONTENT_LIST = [ STRING | URI | counter(IDENT LIST_STYLE_TYPE?) |\n    counters(IDENT STRING LIST_STYLE_TYPE?) | ATTR |\n    target-counter([ STRING | URI ] IDENT LIST_STYLE_TYPE?) |\n    target-counter(ATTR IDENT LIST_STYLE_TYPE?) |\n    target-counters([ STRING | URI ] IDENT STRING LIST_STYLE_TYPE?) |\n    target-counters(ATTR IDENT STRING LIST_STYLE_TYPE?) |\n    target-text([ STRING | URI ] [content | before | after | first-letter]?) |\n    target-text(ATTR [content | before | after | first-letter]?) |\n    leader([ dotted | solid | space ] | STRING ) |\n    open-quote | close-quote | no-open-quote | no-close-quote |\n    content([ text | before | after | first-letter ]?) |\n    string(IDENT [first | start | last | first-except]?) |\n    element(IDENT [first | start | last | first-except]?) ]+;\nCONTENT = normal | none | CONTENT_LIST;\ncontent = CONTENT;\nCOUNTER = [ IDENT INT? ]+ | none;\ncounter-increment = COUNTER;\ncounter-reset = COUNTER;\ncounter-set = COUNTER;\ncursor = COMMA(URI* [ auto | crosshair | default | pointer | move | e-resize | ne-resize | nw-resize |\n    n-resize | se-resize | sw-resize | s-resize | w-resize | text | wait | help | progress ]);\ndirection = ltr | rtl;\ndisplay = inline | block | list-item | inline-block | table | inline-table | table-row-group |\n    table-header-group | table-footer-group | table-row | table-column-group | table-column |\n    table-cell | table-caption | none | oeb-page-head | oeb-page-foot | flex | inline-flex |\n    ruby | ruby-base | ruby-text | ruby-base-container | ruby-text-container | run-in | compact | marker |\n    flow-root | grid | inline-grid | contents;\nempty-cells = show | hide;\nFAMILY = SPACE(IDENT+) | STRING;\nFAMILY_LIST = COMMA( FAMILY+ );\nfont-family = FAMILY_LIST;\nfont-size = xx-small | x-small | small | medium | large | x-large | xx-large | larger | smaller | PPLENGTH;\nfont-style = normal | italic | oblique;\nfont-weight = normal | bold | bolder | lighter | POS_NUM;\nheight = PAPLENGTH | MIN_MAX_FIT_CONTENT;\nleft = APLENGTH;\nletter-spacing = normal | LENGTH_OR_NUM;\nline-height = normal | POS_NUM | PPLENGTH;\nlist-style-image = IMAGE;\nlist-style-position = inside | outside;\nlist-style-type = LIST_STYLE_TYPE;\nmargin-right = APLENGTH;\nmargin-left = APLENGTH;\nmargin-top = APLENGTH;\nmargin-bottom = APLENGTH;\nNPLENGTH = none | PLENGTH;\nmax-height = NPLENGTH | MIN_MAX_FIT_CONTENT;\nmax-width = NPLENGTH | MIN_MAX_FIT_CONTENT;\nmin-height = APLENGTH | MIN_MAX_FIT_CONTENT;\nmin-width = APLENGTH | MIN_MAX_FIT_CONTENT;\norphans = POS_INT;\noutline-offset = LENGTH;\noutline-color = COLOR | invert;\noutline-style = BORDER_SIDE_STYLE;\noutline-width = BORDER_SIDE_WIDTH;\noverflow = visible | hidden | scroll | auto | clip;\npadding-right = PPLENGTH;\npadding-left = PPLENGTH;\npadding-top = PPLENGTH;\npadding-bottom = PPLENGTH;\nPAGE_BREAK = auto | always | avoid | left | right | recto | verso;\npage-break-after = PAGE_BREAK;\npage-break-before = PAGE_BREAK;\npage-break-inside = avoid | auto;\nposition = static | relative | absolute | fixed | running(IDENT);\nquotes = [STRING STRING]+ | none | auto;\nright = APLENGTH;\ntable-layout = auto | fixed;\ntext-align = left | right | center | justify | start | end | match-parent | inside | outside;\ntext-indent = PLENGTH;\ntext-transform = capitalize | uppercase | lowercase | none;\ntop = APLENGTH;\nvertical-align = baseline | sub | super | top | text-top | middle | bottom | text-bottom | PLENGTH;\nvisibility = visible | hidden | collapse;\nwhite-space = normal | pre | nowrap | pre-wrap | pre-line | break-spaces;\nwidows = POS_INT;\nwidth = PAPLENGTH | MIN_MAX_FIT_CONTENT;\nword-spacing = normal | LENGTH_OR_NUM;\nz-index = auto | INT;\n\n[epub,moz,webkit]hyphens = auto | manual | none;\n[webkit]hyphenate-character = auto | STRING;\n\n/* css-logical */\nmargin-block-start = APLENGTH;\nmargin-block-end = APLENGTH;\nmargin-inline-start = APLENGTH;\nmargin-inline-end = APLENGTH;\npadding-block-start = APLENGTH;\npadding-block-end = APLENGTH;\npadding-inline-start = APLENGTH;\npadding-inline-end = APLENGTH;\nborder-block-start-color = BORDER_SIDE_COLOR;\nborder-block-end-color = BORDER_SIDE_COLOR;\nborder-inline-start-color = BORDER_SIDE_COLOR;\nborder-inline-end-color = BORDER_SIDE_COLOR;\nborder-block-start-style = BORDER_SIDE_STYLE;\nborder-block-end-style = BORDER_SIDE_STYLE;\nborder-inline-start-style = BORDER_SIDE_STYLE;\nborder-inline-end-style = BORDER_SIDE_STYLE;\nborder-block-start-width = BORDER_SIDE_WIDTH;\nborder-block-end-width = BORDER_SIDE_WIDTH;\nborder-inline-start-width = BORDER_SIDE_WIDTH;\nborder-inline-end-width = BORDER_SIDE_WIDTH;\nblock-start = APLENGTH;\nblock-end = APLENGTH;\ninline-start = APLENGTH;\ninline-end = APLENGTH;\nblock-size = PAPLENGTH | MIN_MAX_FIT_CONTENT;\ninline-size = PAPLENGTH | MIN_MAX_FIT_CONTENT;\nmax-block-size = NPLENGTH | MIN_MAX_FIT_CONTENT;\nmax-inline-size = NPLENGTH | MIN_MAX_FIT_CONTENT;\nmin-block-size = APLENGTH | MIN_MAX_FIT_CONTENT;\nmin-inline-size = APLENGTH | MIN_MAX_FIT_CONTENT;\n\nmargin-inside = auto | APLENGTH;\nmargin-outside = auto | APLENGTH;\npadding-inside = PPLENGTH;\npadding-outside = PPLENGTH;\nborder-inside-color = BORDER_SIDE_COLOR;\nborder-outside-color = BORDER_SIDE_COLOR;\nborder-inside-style = BORDER_SIDE_STYLE;\nborder-outside-style = BORDER_SIDE_STYLE;\nborder-inside-width = BORDER_SIDE_WIDTH;\nborder-outside-width = BORDER_SIDE_WIDTH;\ninside = APLENGTH;\noutside = APLENGTH;\n\nSHAPE = auto | rectangle( PLENGTH{4} ) |  ellipse( PLENGTH{4} ) |  circle( PLENGTH{3} ) |\n    polygon( SPACE(PLENGTH+)+ );\n[epubx]shape-inside = SHAPE;\n[epubx,webkit]shape-outside = SHAPE;\n[epubx]wrap-flow = auto | both | start | end | maximum | clear | around /* epub al */;\n\nTRANSFORM_FUNCTION = matrix(NUM{6}) | translate(PLENGTH{1,2}) | translateX(PLENGTH) | translateY(PLENGTH) |\n scale(NUM{1,2}) | scaleX(NUM) | scaleY(NUM) | rotate(ANGLE) | skewX(ANGLE) | skewY(ANGLE);\n[epub]transform = none | TRANSFORM_FUNCTION+;\n[epub]transform-origin = [[[ top | bottom | left | right] PLENGTH?] | center | PLENGTH]{1,2}; /* relaxed */\n\nBOX = border-box | padding-box | content-box;\nSHADOW = SPACE(inset || LENGTH{2,4} || COLOR); /* relaxed */\n[webkit]background-size = COMMA( SPACE( [PLENGTH | auto ]{1,2} | cover | contain)+ );\n[webkit]background-origin = COMMA( BOX+ );\n[webkit]background-clip = COMMA( BOX+ );\n[webkit]box-shadow = none | COMMA( SHADOW+ );\ntext-shadow = none |  COMMA( SHADOW+ );\n[webkit]box-decoration-break = slice | clone;\nFILTER_FUNCTION = blur(LENGTH) | brightness(NUM | PERCENTAGE) | contrast(NUM | PERCENTAGE) | drop-shadow(SPACE(LENGTH{2,3} COLOR?))\n                | grayscale(NUM | PERCENTAGE) | hue-rotate(ANGLE) | invert(NUM | PERCENTAGE) | opacity(NUM | PERCENTAGE)\n                | saturate(NUM | PERCENTAGE) | sepia(NUM | PERCENTAGE);\nFILTER_FUNCTION_LIST = FILTER_FUNCTION+;\n[webkit]filter = none | FILTER_FUNCTION_LIST;\n\nopacity = NUM;\n\n[moz,webkit]column-width = LENGTH | auto;\n[moz,webkit]column-count = INT | auto;\n[moz,webkit]column-gap = LENGTH | normal;\n[moz,webkit]column-rule-color = COLOR;\n[moz,webkit]column-rule-style = BORDER_SIDE_STYLE;\n[moz,webkit]column-rule-width = BORDER_SIDE_WIDTH;\nBREAK = auto | avoid | avoid-page | page | left | right | recto | verso | avoid-column | column | avoid-region | region;\nbreak-before = BREAK;\nbreak-after = BREAK;\nbreak-inside = auto | avoid | avoid-page | avoid-column | avoid-region;\n[webkit]column-span = none | auto | all;\n[moz]column-fill = auto | balance | balance-all;\nmargin-break = auto | keep | discard;\n\nsrc = COMMA([SPACE(URI format(STRING+)?) | local(FAMILY)]+); /* for font-face */\n\n[epubx,webkit]flow-from = IDENT;\n[epubx,webkit]flow-into = IDENT;\n[epubx]flow-linger = INT | none;\n[epubx]flow-priority = INT;\n[epubx]flow-options = none | [ exclusive || last || static ];\n[epubx]page = INT | auto | IDENT; /* page: IDENT is for CSS Paged Media */\n[epubx]min-page-width = LENGTH;\n[epubx]min-page-height = LENGTH;\n[epubx]required = true | false;\n[epubx]enabled = true | false;\n[epubx]conflicting-partitions = COMMA(IDENT+);\n[epubx]required-partitions = COMMA(IDENT+);\n[epubx]snap-height = LENGTH | none;\n[epubx]snap-width = LENGTH | none;\n[epubx]flow-consume = all | some;\n[epubx]utilization = NUM;\n[epubx]text-zoom = font-size | scale;\n\n[adapt]template = URI_OR_NONE | footnote;\n[adapt]behavior = IDENT;\n\n/* CSS Fonts */\nCOMMON_LIG_VALUES        = [ common-ligatures | no-common-ligatures ];\nDISCRETIONARY_LIG_VALUES = [ discretionary-ligatures | no-discretionary-ligatures ];\nHISTORICAL_LIG_VALUES    = [ historical-ligatures | no-historical-ligatures ];\nCONTEXTUAL_ALT_VALUES    = [ contextual | no-contextual ];\nfont-variant-ligatures = normal | none | [ COMMON_LIG_VALUES || DISCRETIONARY_LIG_VALUES || HISTORICAL_LIG_VALUES || CONTEXTUAL_ALT_VALUES ];\nfont-variant-caps = normal | small-caps | all-small-caps | petite-caps | all-petite-caps | unicase | titling-caps;\nNUMERIC_FIGURE_VALUES   = [ lining-nums | oldstyle-nums ];\nNUMERIC_SPACING_VALUES  = [ proportional-nums | tabular-nums ];\nNUMERIC_FRACTION_VALUES = [ diagonal-fractions | stacked-fractions ];\nfont-variant-numeric = normal | [ NUMERIC_FIGURE_VALUES || NUMERIC_SPACING_VALUES || NUMERIC_FRACTION_VALUES || ordinal || slashed-zero ];\nEAST_ASIAN_VARIANT_VALUES = [ jis78 | jis83 | jis90 | jis04 | simplified | traditional ];\nEAST_ASIAN_WIDTH_VALUES   = [ full-width | proportional-width ];\nfont-variant-east-asian = normal | [ EAST_ASIAN_VARIANT_VALUES || EAST_ASIAN_WIDTH_VALUES || ruby ];\nfont-variant_css2 = normal | small-caps; /* for font shorthand */\nfont-size-adjust = none | NNEG_NUM;\n[webkit]font-kerning = auto | normal | none;\nfont-feature-settings = COMMA( normal | SPACE( STRING [ on | off | INT ]? )+ );\nFONT_STRETCH_CSS3_VALUES = normal | wider | narrower | ultra-condensed | extra-condensed | condensed | semi-condensed | semi-expanded | expanded | extra-expanded | ultra-expanded;\nfont-stretch = FONT_STRETCH_CSS3_VALUES | PERCENTAGE;\nfont-stretch_css3 = FONT_STRETCH_CSS3_VALUES; /* for font shorthand */\nfont-display = [ auto | block | swap | fallback | optional ];\nunicode-range = COMMA( URANGE+ );\n\n/* CSS Images */\nimage-resolution = RESOLUTION;\nobject-fit = fill | contain | cover | none | scale-down;\nobject-position = COMMA( SPACE(BG_POSITION_TERM{1,4})+ ); /* relaxed */\n\n/* CSS Paged Media */\nPAGE_SIZE = a10 | a9 | a8 | a7 | a6 | a5 | a4 | a3 | a2 | a1 | a0\n          | b10 | b9 | b8 | b7 | b6 | b5 | b4 | b3 | b2 | b1 | b0\n          | c10 | c9 | c8 | c7 | c6 | c5 | c4 | c3 | c2 | c1 | c0\n          | jis-b10 | jis-b9 | jis-b8 | jis-b7 | jis-b6 | jis-b5 | jis-b4 | jis-b3 | jis-b2 | jis-b1 | jis-b0\n          | letter | legal | ledger;\nbleed = auto | LENGTH;\nmarks = none | [ crop || cross ];\nsize = POS_LENGTH{1,2} | auto | [ PAGE_SIZE || [ portrait | landscape ] ];\ncrop-offset = auto | LENGTH;\ncrop-marks-line-color = auto | COLOR;\n\n/* CSS Page Floats */\nclear = none | left | right | top | bottom | inline-start | inline-end | block-start | block-end | inside | outside | both | all | same | column | region | page;\nfloat-reference = inline | column | region | page;\nfloat = none | footnote | [ block-start || block-end || inline-start || inline-end || snap-block || snap-inline || left || right || top || bottom || inside || outside ];\nfloat-min-wrap-block = PPLENGTH;\n\n/* CSS Ruby */\nruby-align = start | center | space-between | space-around;\nruby-position = over | under | inter-character;\n\n/* CSS Size Adjust */\n[moz,webkit]text-size-adjust = auto | none | POS_PERCENTAGE;\n\n/* CSS Text */\n[webkit]line-break = auto | loose | normal | strict | anywhere;\noverflow-wrap = normal | break-word | anywhere;\n[moz]tab-size = NNEG_INT | NNEG_LENGTH;\n[moz]text-align-last = auto | start | end | left | right | center | justify | inside | outside;\ntext-justify = auto | none | inter-word | inter-character;\nword-break = normal | keep-all | break-all | break-word;\ntext-spacing-trim = auto | normal | space-all | trim-both | trim-auto |\n    [[ trim-start | space-start | space-first ] ||\n     [ trim-end | space-end | allow-end ] ||\n     [ trim-adjacent | space-adjacent ]];\ntext-autospace = normal | auto | no-autospace |\n    [[ ideograph-alpha || ideograph-numeric || punctuation ] || [ insert | replace ]];\nhanging-punctuation = none | [ first || [ force-end | allow-end ] || last ];\n\n/* CSS Text Decoration */\n[webkit]text-decoration-color = COLOR;\n[webkit]text-decoration-line = none | [ underline || overline || line-through || blink ];\n[webkit]text-decoration-skip = none | [ objects || spaces || ink || edges || box-decoration ];\n[webkit]text-decoration-style = solid | double | dotted | dashed | wavy;\n[webkit]text-decoration-thickness = from-font | APLENGTH;\n[epub,webkit]text-emphasis-color = COLOR;\n[webkit]text-emphasis-position = [ over | under ] [ right | left ];\n[epub,webkit]text-emphasis-style = none | [[ filled | open ] || [ dot | circle | double-circle | triangle | sesame ]] | STRING;\n[webkit]text-underline-position = auto | [ under || [ left | right ]];\n\n/* CSS Transforms */\n[webkit]backface-visibility = visible | hidden;\n\n/* CSS UI */\n[moz,webkit]box-sizing = content-box | padding-box | border-box;\ntext-overflow = [clip | ellipsis | STRING]{1,2};\n\n/* CSS Writing Modes */\n[epub,webkit]text-combine = none | horizontal;\ntext-combine-upright = none | all; /* relaxed */\n[epub,webkit]text-orientation = mixed | upright | sideways-right | sideways-left | sideways | use-glyph-orientation /* the following values are kept for backward-compatibility */ | vertical-right | rotate-right | rotate-left | rotate-normal | auto;\nunicode-bidi = normal | embed | isolate | bidi-override | isolate-override | plaintext;\n[epub,webkit]writing-mode = horizontal-tb | vertical-rl | lr-tb | rl-tb | tb-rl | lr | rl | tb;\n\n/* CSS Flex box */\nFLEX_BASIS = content | PAPLENGTH;\nflex-direction = row | row-reverse | column | column-reverse;\nflex-wrap = nowrap | wrap | wrap-reverse;\norder = INT;\nflex-grow = NNEG_NUM;\nflex-shrink = NNEG_NUM;\nflex-basis = FLEX_BASIS;\nflex = none | [ [ NNEG_NUM NNEG_NUM? ] || FLEX_BASIS ];\njustify-content = flex-start | flex-end | center | space-between | space-around;\nalign-items = flex-start | flex-end | center | baseline | stretch;\nalign-self = auto | flex-start | flex-end | center | baseline | stretch;\nalign-content = flex-start | flex-end | center | space-between | space-around | stretch;\n\n/* Pointer Events */\ntouch-action = auto | none | [ pan-x || pan-y ] | manipulation;\n\n/* SVG 2 */\nOPACITY_VALUE = NUM | PERCENTAGE;\nDASH_ARRAY = COMMA( SPACE( [ LENGTH | PERCENTAGE | NUM ]+ )+ );\nPAINT = none | child | child(INT) | COLOR | SPACE( URI [none | COLOR]? ) | context-fill | context-stroke;\ncolor-interpolation = auto | sRGB | linearRGB;\ncolor-rendering = auto | optimizeSpeed | optimizeQuality;\nfill = PAINT;\nfill-opacity = OPACITY_VALUE;\nfill-rule = nonzero | evenodd;\nglyph-orientation-vertical = auto | NUM | ANGLE;\nimage-rendering = auto | optimizeSpeed | optimizeQuality | crisp-edges | pixelated;\nmarker-start = none | URI;\nmarker-mid = none | URI;\nmarker-end = none | URI;\npointer-events = bounding-box | visiblePainted | visibleFill | visibleStroke | visible | painted | fill | stroke | all | none;\npaint-order = normal | [ fill || stroke || markers ];\nshape-rendering = auto | optimizeSpeed | crispEdges | geometricPrecision;\nstop-color = COLOR;\nstop-opacity = OPACITY_VALUE;\nstroke = PAINT;\nstroke-dasharray = none | DASH_ARRAY;\nstroke-dashoffset = PERCENTAGE | LENGTH_OR_NUM;\nstroke-linecap = butt | round | square;\nstroke-linejoin = miter | round | bevel;\nstroke-miterlimit = NUM;\nstroke-opacity = OPACITY_VALUE;\nstroke-width = PERCENTAGE | LENGTH_OR_NUM;\ntext-anchor = start | middle | end;\ntext-rendering = auto | optimizeSpeed | optimizeLegibility | geometricPrecision;\nvector-effect = none | SPACE( [ non-scaling-stroke | non-scaling-size | non-rotation | fixed-position ]+ [ viewport | screen ]? );\n\n/* SVG 1.1 */\nalignment-baseline = auto | baseline | before-edge | text-before-edge | middle | central | after-edge | text-after-edge | ideographic | alphabetic | hanging | mathematical;\nbaseline-shift = baseline | sub | super | PERCENTAGE | LENGTH_OR_NUM;\ndominant-baseline = auto | use-script | no-change | reset-size | ideographic | alphabetic | hanging | mathematical | central | middle | text-after-edge | text-before-edge;\nmask = none | URI;\n\n/* css-masking-1 */\nSHAPE_RADIUS = PLENGTH | closest-side | farthest-side;\nFILL_RULE = nonzero | evenodd;\nSHAPE_BOX = BOX | margin-box;\nGEOMETRY_BOX = SHAPE_BOX | fill-box | stroke-box | view-box;\nBASIC_SHAPE =\n    inset( SPACE( PLENGTH{1,4} [ round PLENGTH{1,4} [ SLASH PLENGTH{1,4} ]? ]? ) )\n  | circle(  SPACE( [SHAPE_RADIUS]?    [at BG_POSITION_TERM{1,4}]? ) )\n  | ellipse( SPACE( SHAPE_RADIUS{2}? [at BG_POSITION_TERM{1,4}]? ) )\n  | polygon( FILL_RULE? COMMA( SPACE( PLENGTH{2} )+ )+ );\n[webkit]clip-path = none | URI | [ BASIC_SHAPE || GEOMETRY_BOX ];\nclip-rule = nonzero | evenodd;\n\n/* filters */\nflood-color = COLOR;\nflood-opacity = OPACITY_VALUE;\nlighting-color = COLOR;\n\n/* compositing-1 */\nBLEND_MODE = normal | multiply | screen | overlay | darken | lighten | color-dodge | color-burn | hard-light | soft-light | difference | exclusion | hue | saturation | color | luminosity;\nmix-blend-mode = BLEND_MODE;\nisolation = auto | isolate;\nbackground-blend-mode = COMMA( BLEND_MODE+ );\n\n/* CSS GCPM */\nstring-set = COMMA( SPACE( IDENT CONTENT_LIST )+ | none );\nfootnote-policy = auto | line;\n\n/* CSS Repeated Headers and Footers */\n[viv]repeat-on-break = auto | none | header | footer;\n\n/* Compatibility */\n[webkit]text-fill-color = COLOR;\n[webkit]text-stroke-color = COLOR;\n[webkit]text-stroke-width = BORDER_SIDE_WIDTH;\n\nDEFAULTS\n\nbackground-attachment: scroll;\nbackground-color: transparent;\nbackground-image: none;\nbackground-repeat: repeat;\nbackground-position: 0% 0%;\nbackground-clip: border-box;\nbackground-origin: padding-box;\nbackground-size: auto;\nborder-top-color: currentColor;\nborder-right-color: currentColor;\nborder-bottom-color: currentColor;\nborder-left-color: currentColor;\nborder-top-style: none;\nborder-right-style: none;\nborder-bottom-style: none;\nborder-left-style: none;\nborder-top-width: 3px;\nborder-right-width: 3px;\nborder-bottom-width: 3px;\nborder-left-width: 3px;\nborder-top-left-radius: 0;\nborder-top-right-radius: 0;\nborder-bottom-right-radius: 0;\nborder-bottom-left-radius: 0;\nborder-image-source: none;\nborder-image-slice: 100%;\nborder-image-width: 1;\nborder-image-outset: 0;\nborder-image-repeat: stretch;\ncolumn-count: auto;\ncolumn-gap: normal;\ncolumn-width: auto;\ncolumn-rule-color: currentColor;\ncolumn-rule-style: none;\ncolumn-rule-width: 3px;\ncolumn-fill: balance;\noutline-color: currentColor;\noutline-style: none;\noutline-width: 3px;\nflex-direction: row;\nflex-wrap: nowrap;\nfont-family: serif;\nfont-style: normal;\nfont-size: medium;\nfont-size-adjust: none;\nfont-kerning: auto;\nfont-feature-settings: normal;\nfont-variant-ligatures: normal;\nfont-variant-caps: normal;\nfont-variant-numeric: normal;\nfont-variant-east-asian: normal;\nfont-weight: normal;\nfont-stretch: normal;\nline-height: normal;\nlist-style-image: none;\nlist-style-position: outside;\nlist-style-type: disc;\nmargin-bottom: auto;\nmargin-left: auto;\nmargin-right: auto;\nmargin-top: auto;\npadding-bottom: auto;\npadding-left: auto;\npadding-right: auto;\npadding-top: auto;\ntext-autospace: normal;\ntext-emphasis-color: currentColor;\ntext-emphasis-style: none;\ntext-spacing-trim: normal;\ntext-stroke-color: currentColor;\ntext-stroke-width: 0;\nmarker-start: none;\nmarker-mid: none;\nmarker-end: none;\n\n/* css-logical */\nborder-block-start-color: currentColor;\nborder-block-end-color: currentColor;\nborder-inline-start-color: currentColor;\nborder-inline-end-color: currentColor;\nborder-inside-color: currentColor;\nborder-outside-color: currentColor;\nborder-block-start-style: none;\nborder-block-end-style: none;\nborder-inline-start-style: none;\nborder-inline-end-style: none;\nborder-inside-style: none;\nborder-outside-style: none;\nborder-block-start-width: 3px;\nborder-block-end-width: 3px;\nborder-inline-start-width: 3px;\nborder-inline-end-width: 3px;\nborder-inside-width: 3px;\nborder-outside-width: 3px;\n\nSHORTHANDS\n\nall = ALL;\nbackground = COMMA background-image [background-position [ / background-size ]] background-repeat\n     background-attachment [background-origin background-clip] background-color; /* background-color is a special case, see the code */\nborder-top = border-top-width border-top-style border-top-color;\nborder-right = border-right-width border-right-style border-right-color;\nborder-bottom = border-bottom-width border-bottom-style border-bottom-color;\nborder-left = border-left-width border-left-style border-left-color;\nborder-inside = border-inside-width border-inside-style border-inside-color;\nborder-outside = border-outside-width border-outside-style border-outside-color;\nborder-width = INSETS border-top-width border-right-width border-bottom-width border-left-width;\nborder-style = INSETS border-top-style border-right-style border-bottom-style border-left-style;\nborder-color = INSETS border-top-color border-right-color border-bottom-color border-left-color;\nborder = border-width border-style border-color;\nborder-image = border-image-source border-image-slice [ / border-image-width [ / border-image-outset ] ]\n     border-image-repeat;\nborder-radius = INSETS_SLASH border-top-left-radius border-top-right-radius\n     border-bottom-right-radius border-bottom-left-radius;\n[moz,webkit]columns = column-width column-count;\n[moz,webkit]column-rule = column-rule-width column-rule-style column-rule-color;\nflex-flow = flex-direction flex-wrap;\noeb-column-number = column-count;\noutline = outline-width outline-style outline-color;\nlist-style = list-style-position list-style-type list-style-image;\nmargin = INSETS margin-top margin-right margin-bottom margin-left;\npadding = INSETS padding-top padding-right padding-bottom padding-left;\nfont = FONT font-style font-variant_css2 font-weight font-stretch_css3 /* font-size line-height font-family are special-cased */;\nfont-variant = font-variant-ligatures font-variant-caps font-variant-numeric font-variant-east-asian;\n[epub,webkit]text-emphasis = text-emphasis-style text-emphasis-color;\nmarker = INSETS marker-start marker-mid marker-end;\n[webkit]text-stroke = text-stroke-width text-stroke-color;\ntext-decoration = text-decoration-line text-decoration-color text-decoration-style text-decoration-thickness;\ntext-spacing = TEXT_SPACING text-autospace text-spacing-trim;\n\n/* css-logical */\nmargin-block = INSETS margin-block-start margin-block-end;\nmargin-inline = INSETS margin-inline-start margin-inline-end;\npadding-block = INSETS padding-block-start padding-block-end;\npadding-inline = INSETS padding-inline-start padding-inline-end;\nborder-block-width = INSETS border-block-start-width border-block-end-width;\nborder-block-style = INSETS border-block-start-style border-block-end-style;\nborder-block-color = INSETS border-block-start-color border-block-end-color;\nborder-inline-width = INSETS border-inline-start-width border-inline-end-width;\nborder-inline-style = INSETS border-inline-start-style border-inline-end-style;\nborder-inline-color = INSETS border-inline-start-color border-inline-end-color;\nborder-block = border-block-width border-block-style border-block-color;\nborder-inline = border-inline-width border-inline-style border-inline-color;\nborder-block-start = border-block-start-width border-block-start-style border-block-start-color;\nborder-block-end = border-block-end-width border-block-end-style border-block-end-color;\nborder-inline-start = border-inline-start-width border-inline-start-style border-inline-start-color;\nborder-inline-end = border-inline-end-width border-inline-end-style border-inline-end-color;\ninset-block-start = block-start;\ninset-block-end = block-end;\ninset-inline-start = inline-start;\ninset-inline-end = inline-end;\ninset-inside = inside;\ninset-outside = outside;\ninset-block = INSETS block-start block-end;\ninset-inline = INSETS inline-start inline-end;\ninset = INSETS top right bottom left;\n\n/* old names  */\nword-wrap = overflow-wrap;\n[adapt,webkit]margin-before = margin-block-start;\n[adapt,webkit]margin-after = margin-block-end;\n[adapt,webkit]margin-start = margin-inline-start;\n[adapt,webkit]margin-end = margin-inline-end;\n[adapt,webkit]padding-before = padding-block-start;\n[adapt,webkit]padding-after = padding-block-end;\n[adapt,webkit]padding-start = padding-inline-start;\n[adapt,webkit]padding-end = padding-inline-end;\n[adapt,webkit]border-before-color = border-block-start-color;\n[adapt,webkit]border-after-color = border-block-end-color;\n[adapt,webkit]border-start-color = border-inline-start-color;\n[adapt,webkit]border-end-color = border-inline-end-color;\n[adapt,webkit]border-before-style = border-block-start-style;\n[adapt,webkit]border-after-style = border-block-end-style;\n[adapt,webkit]border-start-style = border-inline-start-style;\n[adapt,webkit]border-end-style = border-inline-end-style;\n[adapt,webkit]border-before-width = border-block-start-width;\n[adapt,webkit]border-after-width = border-block-end-width;\n[adapt,webkit]border-start-width = border-inline-start-width;\n[adapt,webkit]border-end-width = border-inline-end-width;\n[adapt,webkit]before = block-start;\n[adapt,webkit]after = block-end;\n[adapt,webkit]start = inline-start;\n[adapt,webkit]end = inline-end;\n\n",
  xc =
    '\n<!DOCTYPE html>\n<html xmlns="http://www.w3.org/1999/xhtml" xmlns:s="http://www.pyroxy.com/ns/shadow">\n<head>\n<style><![CDATA[\n\n.-vivliostyle-footnote-content {\n  float: footnote;\n}\n\n.-vivliostyle-table-cell-container {\n  display: block;\n}\n\n]]></style>\n</head>\n<body>\n\n<s:template id="footnote"><s:content/><s:include class="-vivliostyle-footnote-content"/></s:template>\n\n<s:template id="table-cell"><div data-vivliostyle-flow-root="true" class="-vivliostyle-table-cell-container"><s:content/></div></s:template>\n\n</body>\n</html>',
  yc =
    '\n@namespace "http://www.w3.org/1999/xhtml";\n\n:root {\n  hyphens: -epubx-expr(pref-hyphenate? "auto": "manual");\n}\n:root[data-vivliostyle-epub-spine-properties~="page-spread-left"] {\n  break-before: left;\n}\n:root[data-vivliostyle-epub-spine-properties~="page-spread-right"] {\n  break-before: right;\n}\n\n@-adapt-footnote-area {\n  display: block;\n  margin-block-start: 0.5em;\n  margin-block-end: 0.5em;\n}\n\n@-adapt-footnote-area ::before {\n  display: block;\n  border-block-start-width: 1px;\n  border-block-start-style: solid;\n  border-block-start-color: black;\n  margin-block-end: 0.4em;\n  margin-inline-start: 0;\n  margin-inline-end: 60%;\n}\n\n/* default page master */\n@-epubx-page-master :background-host {\n  @-epubx-partition :layout-host {\n    -epubx-flow-from: body;\n    top: -epubx-expr(header.margin-bottom-edge);\n    bottom: -epubx-expr(page-height - footer.margin-top-edge);\n    left: 0px;\n    right: 0px;\n    column-width: 25em;\n  }\n  @-epubx-partition footer :oeb-page-foot {\n    writing-mode: horizontal-tb;\n    -epubx-flow-from: oeb-page-foot;\n    bottom: 0px;\n    left: 0px;\n    right: 0px;\n  }\n  @-epubx-partition header :oeb-page-head {\n    writing-mode: horizontal-tb;\n    -epubx-flow-from: oeb-page-head;\n    top: 0px;\n    left: 0px;\n    right: 0px;\n  }\n}\n\n@page {\n  @top-left-corner {\n    text-align: right;\n    vertical-align: middle;\n  }\n  @top-left {\n    text-align: left;\n    vertical-align: middle;\n  }\n  @top-center {\n    text-align: center;\n    vertical-align: middle;\n  }\n  @top-right {\n    text-align: right;\n    vertical-align: middle;\n  }\n  @top-right-corner {\n    text-align: left;\n    vertical-align: middle;\n  }\n  @left-top {\n    text-align: center;\n    vertical-align: top;\n  }\n  @left-middle {\n    text-align: center;\n    vertical-align: middle;\n  }\n  @left-bottom {\n    text-align: center;\n    vertical-align: bottom;\n  }\n  @right-top {\n    text-align: center;\n    vertical-align: top;\n  }\n  @right-middle {\n    text-align: center;\n    vertical-align: middle;\n  }\n  @right-bottom {\n    text-align: center;\n    vertical-align: bottom;\n  }\n  @bottom-left-corner {\n    text-align: right;\n    vertical-align: middle;\n  }\n  @bottom-left {\n    text-align: left;\n    vertical-align: middle;\n  }\n  @bottom-center {\n    text-align: center;\n    vertical-align: middle;\n  }\n  @bottom-right {\n    text-align: right;\n    vertical-align: middle;\n  }\n  @bottom-right-corner {\n    text-align: left;\n    vertical-align: middle;\n  }\n}\n\n@media print {\n  @page {\n    margin: 10%;\n  }\n}\n',
  Ec =
    '\n@namespace "http://www.w3.org/1999/xhtml";\n@namespace m "http://www.w3.org/1998/Math/MathML";\n@namespace epub "http://www.idpf.org/2007/ops";\n\nhtml,\naddress,\nblockquote,\nbody,\ndd,\ndiv,\ndl,\ndt,\nfieldset,\nform,\nframe,\nframeset,\nh1,\nh2,\nh3,\nh4,\nh5,\nh6,\nnoframes,\nol,\np,\nul,\ncenter,\ndir,\nhr,\nmenu,\npre,\ndetails,\ndialog,\nlegend,\nlisting,\noptgroup,\noption,\nplaintext,\nsearch,\nxmp,\narticle,\nsection,\nnav,\naside,\nhgroup,\nfooter,\nheader,\nfigure,\nfigcaption,\nmain {\n  display: block;\n}\nli {\n  display: list-item;\n}\nhead {\n  display: none !important;\n}\ntable {\n  display: table;\n}\ntr {\n  display: table-row;\n}\nthead {\n  display: table-header-group;\n  break-after: avoid;\n}\ntbody {\n  display: table-row-group;\n}\ntfoot {\n  display: table-footer-group;\n  break-before: avoid;\n}\ncol {\n  display: table-column;\n}\ncolgroup {\n  display: table-column-group;\n}\ntd,\nth {\n  display: table-cell;\n}\ncaption {\n  display: table-caption;\n  text-align: center;\n}\nth {\n  font-weight: bolder;\n  text-align: center;\n}\n*[hidden],\nlink,\nstyle,\nscript {\n  display: none;\n}\nh1 {\n  font-size: 2em;\n  margin-block: 0.67em;\n}\nh2 {\n  font-size: 1.5em;\n  margin-block: 0.83em;\n}\nh3 {\n  font-size: 1.17em;\n  margin-block: 1em;\n}\nh4 {\n  font-size: 1em;\n  margin-block: 1.33em;\n}\nh5 {\n  font-size: 0.83em;\n  margin-block: 1.67em;\n}\nh6 {\n  font-size: 0.67em;\n  margin-block: 2.33em;\n}\nh1,\nh2,\nh3,\nh4,\nh5,\nh6 {\n  font-weight: bold;\n  break-after: avoid;\n}\np,\nblockquote,\nfigure,\nul,\nol,\ndl,\ndir,\nmenu {\n  margin-block: 1em;\n}\nb,\nstrong {\n  font-weight: bolder;\n}\nblockquote,\nfigure {\n  margin-inline: 40px;\n}\ni,\ncite,\ndfn,\nem,\nvar,\naddress {\n  font-style: italic;\n}\npre,\ntt,\ncode,\nkbd,\nsamp {\n  font-family: monospace;\n  text-spacing: none;\n  hanging-punctuation: none;\n}\nlisting,\nplaintext,\nxmp,\npre {\n  white-space: pre;\n}\npre[wrap] {\n  white-space: pre-wrap;\n}\nbutton,\ntextarea,\ninput,\nselect {\n  display: inline-block;\n}\nbig {\n  font-size: 1.17em;\n}\nsmall,\nsub,\nsup {\n  font-size: 0.83em;\n}\nsub {\n  vertical-align: sub;\n}\nsup {\n  vertical-align: super;\n}\ntable {\n  box-sizing: border-box;\n  border-spacing: 2px;\n  border-collapse: separate;\n  text-indent: initial;\n}\nthead,\ntbody,\ntfoot,\ntable > tr {\n  vertical-align: middle;\n}\ntr,\ntd,\nth {\n  vertical-align: inherit;\n}\ns,\nstrike,\ndel {\n  text-decoration: line-through;\n}\nhr {\n  border-style: inset;\n  border-width: 1px;\n  margin-block: 0.5em;\n}\nhr[color],\nhr[noshade] {\n  border-style: solid;\n}\nol,\nul,\ndir,\nmenu {\n  padding-inline-start: 40px;\n}\ndd {\n  margin-inline-start: 40px;\n}\nol ul,\nul ol,\nul ul,\nol ol {\n  margin-block: 0;\n}\nu,\nins {\n  text-decoration: underline;\n}\ncenter {\n  text-align: center;\n}\nq::before {\n  content: open-quote;\n}\nq::after {\n  content: close-quote;\n}\n\nruby {\n  display: ruby;\n}\nrp {\n  display: none;\n}\nrbc {\n  display: ruby-base-container;\n}\nrtc {\n  display: ruby-text-container;\n}\nrb {\n  display: ruby-base;\n  white-space: nowrap;\n}\nrt {\n  display: ruby-text;\n}\nrtc,\nrt {\n  text-emphasis: none;\n  white-space: nowrap;\n  line-height: 1;\n}\nrtc,\nrt {\n  font-size: 50%;\n}\nrtc:lang(zh-TW),\nrt:lang(zh-TW) {\n  font-size: 30%;\n}\nrtc > rt,\nrtc > rt:lang(zh-TW) {\n  font-size: 100%;\n}\n\n/* Bidi settings */\nbdo[dir="ltr"] {\n  direction: ltr;\n  unicode-bidi: bidi-override;\n}\nbdo[dir="rtl"] {\n  direction: rtl;\n  unicode-bidi: bidi-override;\n}\n*[dir="ltr"] {\n  direction: ltr;\n  unicode-bidi: isolate;\n}\n*[dir="rtl"] {\n  direction: rtl;\n  unicode-bidi: isolate;\n}\n\n/* MathML */\nm|math[display="block"] {\n  display: block;\n  display: block math;\n}\n\n/*------------------ epub-specific ---------------------*/\n\na[epub|type="noteref"],\na[epub\\:type="noteref"] {\n  font-size: 0.75em;\n  vertical-align: super;\n  line-height: 0.01;\n}\n\na[epub|type="noteref"]:href-epub-type(footnote, aside),\na[epub\\:type="noteref"]:href-epub-type(footnote, aside) {\n  -adapt-template: footnote;\n  text-decoration: none;\n}\n\naside[epub|type="footnote"],\naside[epub\\:type="footnote"] {\n  display: none;\n}\n\naside[epub|type="footnote"]:footnote-content,\naside[epub\\:type="footnote"]:footnote-content {\n  display: block;\n  margin: 0.25em;\n  font-size: 1.2em;\n  line-height: 1.2;\n}\n\nepub|trigger {\n  display: none;\n}\n\nepub|switch {\n  display: inline;\n}\n\nepub|default {\n  display: inline;\n}\n\nepub|case {\n  display: none;\n}\n\nepub|case[required-namespace::supported] {\n  display: inline;\n}\n\nepub|case[required-namespace::supported] ~ epub|case {\n  display: none;\n}\n\nepub|case[required-namespace::supported] ~ epub|default {\n  display: none;\n}\n',
  Sc =
    '\n@namespace "http://www.w3.org/1999/xhtml";\n\n*:not([data-vivliostyle-role=doc-toc],\n  [data-vivliostyle-role=doc-toc] *,\n  :has([data-vivliostyle-role=doc-toc]),\n  :is(h1,h2,h3,h4,h5,h6):has(+:not(nav)[data-vivliostyle-role=doc-toc])) {\n  display: none;\n}\n\n[hidden] {\n  display: revert;\n}\n\n[data-vivliostyle-role=doc-toc] li a {\n  -adapt-behavior: toc-node-anchor;\n}\n\n[data-vivliostyle-role=doc-toc] li {\n  -adapt-behavior: toc-node;\n}\n\n[data-vivliostyle-role=doc-toc] li > :not(ul,ol):first-child {\n  -adapt-behavior: toc-node-first-child;\n}\n\n[data-vivliostyle-role=doc-toc] :is(ol,ul),\n[data-vivliostyle-role=doc-toc]:is(ol,ul) {\n  -adapt-behavior: toc-container;\n}\n',
  Nc =
    '\n[data-viv-margin-discard~="block-start"] {\n  margin-block-start: 0 !important;\n}\n[data-viv-margin-discard~="block-end"] {\n  margin-block-end: 0 !important;\n}\n[data-viv-margin-discard~="inline-start"] {\n  margin-inline-start: 0 !important;\n}\n[data-viv-margin-discard~="inline-end"] {\n  margin-inline-end: 0 !important;\n}\n\n[data-viv-box-break~="inline-start"]:not([data-viv-box-break~="clone"]) {\n  margin-inline-start: 0 !important;\n  padding-inline-start: 0 !important;\n  border-inline-start-width: 0 !important;\n  border-start-start-radius: 0 !important;\n  border-end-start-radius: 0 !important;\n}\n[data-viv-box-break~="inline-end"]:not([data-viv-box-break~="clone"]) {\n  margin-inline-end: 0 !important;\n  padding-inline-end: 0 !important;\n  border-inline-end-width: 0 !important;\n  border-start-end-radius: 0 !important;\n  border-end-end-radius: 0 !important;\n}\n[data-viv-box-break~="block-start"]:not([data-viv-box-break~="clone"]):not(table[style*="border-collapse: collapse"]:has(>thead)) {\n  margin-block-start: 0 !important;\n  padding-block-start: 0 !important;\n  border-block-start-width: 0 !important;\n  border-start-start-radius: 0 !important;\n  border-start-end-radius: 0 !important;\n}\n[data-viv-box-break~="block-end"]:not([data-viv-box-break~="clone"]):not(table[style*="border-collapse: collapse"]:has(>tfoot)) {\n  margin-block-end: 0 !important;\n  padding-block-end: 0 !important;\n  border-block-end-width: 0 !important;\n  border-end-start-radius: 0 !important;\n  border-end-end-radius: 0 !important;\n}\n[data-viv-box-break~="block-start"][data-viv-box-break~="text-start"] {\n  text-indent: 0 !important;\n}\n[data-viv-box-break~="block-end"][data-viv-box-break~="text-end"][data-viv-box-break~="justify"] {\n  text-align-last: justify !important;\n}\n[data-viv-box-break~="block-end"][data-viv-box-break~="text-end"][data-viv-box-break~="justify"] > * {\n  text-align-last: auto;\n}\n[data-viv-box-break~="block-end"][data-viv-box-break~="text-end"]:not([data-viv-box-break~="justify"]) {\n  text-align-last: auto !important;\n}\n\nspan.viv-anonymous-block {\n  display: block;\n}\n\n[data-vivliostyle-page-container] {\n  text-spacing-trim: space-all;\n  text-autospace: no-autospace;\n}\nviv-ts-open.viv-ts-auto > viv-ts-inner,\nviv-ts-open.viv-ts-trim > viv-ts-inner {\n  margin-inline-start: -0.5em;\n}\nviv-ts-close.viv-ts-auto > viv-ts-inner,\nviv-ts-close.viv-ts-trim > viv-ts-inner {\n  letter-spacing: -0.5em;\n}\nviv-ts-close.viv-hang-end > viv-ts-inner,\nviv-ts-close.viv-hang-last > viv-ts-inner {\n  letter-spacing: -1em;\n}\nviv-ts-open.viv-ts-auto::before,\nviv-ts-close.viv-ts-auto::after,\nviv-ts-close.viv-hang-end::after {\n  content: " ";\n  font-family: Courier, monospace;\n  word-spacing: normal;\n  letter-spacing: -0.11em;\n  line-height: 0;\n  text-orientation: mixed;\n  visibility: hidden;\n}\nviv-ts-close.viv-hang-end:not(.viv-hang-hw)::after {\n  letter-spacing: 0.4em;\n}\nviv-ts-close.viv-hang-hw > viv-ts-inner {\n  letter-spacing: -0.5em;\n}\nviv-ts-open.viv-hang-first > viv-ts-inner {\n  display: inline-block;\n  line-height: 1;\n  inline-size: 1em;\n  text-indent: 0;\n  text-align: end;\n  text-align-last: end;\n  margin-inline-start: -1em;\n}\nviv-ts-thin-sp::after {\n  content: " ";\n  font-family: Times, serif;\n  word-spacing: normal;\n  letter-spacing: -0.125em;\n  line-height: 0;\n  text-orientation: mixed;\n  visibility: hidden;\n}\n[style*=text-decoration] :is(viv-ts-thin-sp, viv-ts-close.viv-ts-auto)::after {\n  visibility: visible;\n}\n\nspan[data-viv-leader] {\n  text-combine-upright: none;\n  text-orientation: mixed;\n  white-space: pre;\n}\n';
function bs(e, t, i) {
  let n = A("fetchFromURL"),
    r = { method: i || "GET", mode: "cors" },
    s = n.suspend(),
    o = {
      status: 0,
      statusText: "",
      url: e,
      contentType: null,
      responseText: null,
      responseXML: null,
      responseBlob: null,
    };
  return (
    fetch(e, r)
      .then((i) => {
        var n;
        return (
          (o.status = i.status),
          (o.url = i.url),
          (o.statusText = i.statusText),
          (o.contentType =
            null == (n = i.headers.get("Content-Type"))
              ? void 0
              : n.replace(/;.*$/, "").toLowerCase()),
          i.ok
            ? "blob" === t
              ? i.blob()
              : "arraybuffer" === t
              ? i.arrayBuffer()
              : "json" === t
              ? i.json()
              : /\/aozorabunko\/[^/]+\/cards\/[^/]+\/files\/[^/.]+\.html$/.test(
                  e
                )
              ? ((o.contentType = "text/html"),
                i
                  .arrayBuffer()
                  .then((e) => new TextDecoder("Shift_JIS").decode(e)))
              : (/^data:,(<|%3c)/i.test(e) && (o.contentType = "text/html"),
                i.text())
            : i.text()
        );
      })
      .then((e) => {
        "blob" === t && e instanceof Blob
          ? (o.responseBlob = e)
          : "arraybuffer" === t && e instanceof ArrayBuffer
          ? (o.responseBlob = vc([e]))
          : "json" === t
          ? (o.responseText = JSON.stringify(e))
          : "string" == typeof e && (o.responseText = e),
          s.schedule(o);
      })
      .catch((t) => {
        V.warn(t, `Error fetching ${e}`), s.schedule(o);
      }),
    n.result()
  );
}
function vc(e, t) {
  return new Blob(e, { type: t || "application/octet-stream" });
}
function bh(e) {
  let t = A("readBlob"),
    i = new FileReader(),
    n = t.suspend(i);
  return (
    i.addEventListener(
      "load",
      () => {
        n.schedule(i.result);
      },
      !1
    ),
    i.readAsArrayBuffer(e),
    t.result()
  );
}
function xh(e) {
  return URL.createObjectURL(e);
}
var Cs = class {
  constructor(e, t) {
    (this.parser = e),
      (this.type = t),
      p(this, "resources", {}),
      p(this, "fetchers", {});
  }
  load(e, t, i) {
    e = kt(e);
    let n = this.resources[e];
    return void 0 !== n ? T(n) : this.fetch(e, t, i).get();
  }
  fetchInner(e, t, i) {
    let n = A("fetch"),
      r = e.endsWith("?viv-toc-box");
    r && (e = e.replace("?viv-toc-box", ""));
    let s = J("user-agent.xml", qt),
      o = !r && e === s;
    return (
      o && (e = `data:application/xml,${encodeURIComponent(xc)}`),
      bs(e, this.type).then((a) => {
        if (a.status >= 400 && t)
          throw new Error(
            (i || `Failed to fetch required resource: ${e}`) +
              ` (${a.status}${a.statusText ? " " + a.statusText : ""})`
          );
        r
          ? ((e += "?viv-toc-box"), (a.url += "?viv-toc-box"))
          : o && (a.url = e = s),
          this.parser(a, this).then((t) => {
            delete this.fetchers[e], (this.resources[e] = t), n.finish(t);
          });
      }),
      n.result()
    );
  }
  fetch(e, t, i) {
    if (((e = kt(e)), this.resources[e])) return null;
    let n = this.fetchers[e];
    return (
      n ||
        ((n = new tn(() => this.fetchInner(e, t, i), `Fetch ${e}`)),
        (this.fetchers[e] = n),
        n.start()),
      n
    );
  }
  get(e) {
    return this.resources[kt(e)];
  }
  delete(e) {
    delete this.resources[kt(e)];
  }
};
function _m(e, t) {
  let i = e.responseText;
  return T(i ? Xs(i) : null);
}
function yh() {
  return new Cs(_m, "text");
}
function On(e, t, i) {
  let n = new tn(() => {
    let n = A("loadElement"),
      r = n.suspend(e),
      s = !1,
      o = (e) => {
        s || ((s = !0), r.schedule(e ? e.type : "timeout"));
      };
    return (
      e.addEventListener("load", o, !1),
      e.addEventListener("error", o, !1),
      e.addEventListener("abort", o, !1),
      "http://www.w3.org/2000/svg" == e.namespaceURI
        ? (t &&
            e.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", t),
          setTimeout(o, 300))
        : "script" === e.localName
        ? setTimeout(o, 3e3)
        : t && ((e.src = t), i && (e.alt = i)),
      n.result()
    );
  }, `loadElement ${t || e.localName}`);
  return n.start(), n;
}
var Eh = 0,
  Um = 16777216,
  Tc = 33554432,
  Ur = 50331648,
  Hr = 67108864,
  Hm = 83886080,
  zm = 100663296,
  ei = class {
    constructor(e) {
      (this.scope = e), p(this, "flavor"), (this.flavor = "Author");
    }
    getCurrentToken() {
      return null;
    }
    getScope() {
      return this.scope;
    }
    error(e, t) {}
    startStylesheet(e) {
      this.flavor = e;
    }
    tagSelector(e, t) {}
    classSelector(e) {}
    pseudoclassSelector(e, t) {}
    pseudoelementSelector(e, t) {}
    idSelector(e) {}
    attributeSelector(e, t, i, n) {}
    descendantSelector() {}
    childSelector() {}
    adjacentSiblingSelector() {}
    followingSiblingSelector() {}
    nextSelector() {}
    startSelectorRule() {}
    startFontFaceRule() {}
    startFootnoteRule(e) {}
    startViewportRule() {}
    startDefineRule() {}
    startRegionRule() {}
    startPageRule() {}
    startPageMarginBoxRule(e) {}
    startWhenRule(e) {}
    startMediaRule(e) {
      this.startWhenRule(e);
    }
    startFlowRule(e) {}
    startPageTemplateRule() {}
    startPageMasterRule(e, t, i) {}
    startPartitionRule(e, t, i) {}
    startPartitionGroupRule(e, t, i) {}
    startRuleBody() {}
    property(e, t, i) {}
    endRule() {}
    startFuncWithSelector(e) {}
    endFuncWithSelector() {}
    pushSelectorText(e) {}
    getImportantSpecificity() {
      switch (this.flavor) {
        case "UA":
          return Eh;
        case "User":
          return zm;
        default:
          return Hm;
      }
    }
    getBaseSpecificity() {
      switch (this.flavor) {
        case "UA":
          return Eh;
        case "User":
          return Um;
        default:
          return Tc;
      }
    }
  },
  Dr = class extends ei {
    constructor() {
      super(null),
        p(this, "stack", []),
        p(this, "tokenizer", null),
        p(this, "slave", null);
    }
    pushHandler(e) {
      this.stack.push(this.slave), (this.slave = e);
    }
    popHandler() {
      this.slave = this.stack.pop();
    }
    getCurrentToken() {
      return this.tokenizer ? this.tokenizer.token() : null;
    }
    getScope() {
      return this.slave.getScope();
    }
    error(e, t) {
      this.slave.error(e, t);
    }
    errorMsg(e, t) {
      var i;
      V.warn(e, null != (i = null == t ? void 0 : t.toString()) ? i : "");
    }
    startStylesheet(e) {
      super.startStylesheet(e),
        this.stack.length > 0 &&
          ((this.slave = this.stack[0]), (this.stack = [])),
        this.slave.startStylesheet(e);
    }
    tagSelector(e, t) {
      this.slave.tagSelector(e, t);
    }
    classSelector(e) {
      this.slave.classSelector(e);
    }
    pseudoclassSelector(e, t) {
      this.slave.pseudoclassSelector(e, t);
    }
    pseudoelementSelector(e, t) {
      this.slave.pseudoelementSelector(e, t);
    }
    idSelector(e) {
      this.slave.idSelector(e);
    }
    attributeSelector(e, t, i, n) {
      this.slave.attributeSelector(e, t, i, n);
    }
    descendantSelector() {
      this.slave.descendantSelector();
    }
    childSelector() {
      this.slave.childSelector();
    }
    adjacentSiblingSelector() {
      this.slave.adjacentSiblingSelector();
    }
    followingSiblingSelector() {
      this.slave.followingSiblingSelector();
    }
    nextSelector() {
      this.slave.nextSelector();
    }
    startSelectorRule() {
      this.slave.startSelectorRule();
    }
    startFontFaceRule() {
      this.slave.startFontFaceRule();
    }
    startFootnoteRule(e) {
      this.slave.startFootnoteRule(e);
    }
    startViewportRule() {
      this.slave.startViewportRule();
    }
    startDefineRule() {
      this.slave.startDefineRule();
    }
    startRegionRule() {
      this.slave.startRegionRule();
    }
    startPageRule() {
      this.slave.startPageRule();
    }
    startPageMarginBoxRule(e) {
      this.slave.startPageMarginBoxRule(e);
    }
    startWhenRule(e) {
      this.slave.startWhenRule(e);
    }
    startFlowRule(e) {
      this.slave.startFlowRule(e);
    }
    startPageTemplateRule() {
      this.slave.startPageTemplateRule();
    }
    startPageMasterRule(e, t, i) {
      this.slave.startPageMasterRule(e, t, i);
    }
    startPartitionRule(e, t, i) {
      this.slave.startPartitionRule(e, t, i);
    }
    startPartitionGroupRule(e, t, i) {
      this.slave.startPartitionGroupRule(e, t, i);
    }
    startRuleBody() {
      this.slave.startRuleBody();
    }
    property(e, t, i) {
      this.slave.property(e, t, i);
    }
    endRule() {
      this.slave.endRule();
    }
    startFuncWithSelector(e) {
      this.slave.startFuncWithSelector(e);
    }
    endFuncWithSelector() {
      this.slave.endFuncWithSelector();
    }
    pushSelectorText(e) {
      this.slave.pushSelectorText(e);
    }
  },
  Mr = class extends ei {
    constructor(e, t, i) {
      super(e),
        (this.owner = t),
        (this.topLevel = i),
        p(this, "depth", 0),
        t && (this.flavor = t.flavor);
    }
    getCurrentToken() {
      var e;
      return null == (e = this.owner) ? void 0 : e.getCurrentToken();
    }
    error(e, t) {
      var i;
      null == (i = this.owner) || i.errorMsg(e, t);
    }
    startRuleBody() {
      this.depth++;
    }
    endRule() {
      0 == --this.depth && !this.topLevel && this.owner.popHandler();
    }
  },
  nn = class extends Mr {
    constructor(e, t, i) {
      super(e, t, i);
    }
    report(e) {
      this.error(e, this.getCurrentToken());
    }
    reportAndSkip(e) {
      this.report(e),
        this.owner.pushHandler(new Mr(this.scope, this.owner, !1));
    }
    startSelectorRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_SELECTOR");
    }
    startFontFaceRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_FONT_FACE");
    }
    startFootnoteRule(e) {
      this.reportAndSkip("E_CSS_UNEXPECTED_FOOTNOTE");
    }
    startViewportRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_VIEWPORT");
    }
    startDefineRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_DEFINE");
    }
    startRegionRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_REGION");
    }
    startPageRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_PAGE");
    }
    startWhenRule(e) {
      this.reportAndSkip("E_CSS_UNEXPECTED_WHEN");
    }
    startFlowRule(e) {
      this.reportAndSkip("E_CSS_UNEXPECTED_FLOW");
    }
    startPageTemplateRule() {
      this.reportAndSkip("E_CSS_UNEXPECTED_PAGE_TEMPLATE");
    }
    startPageMasterRule(e, t, i) {
      this.reportAndSkip("E_CSS_UNEXPECTED_PAGE_MASTER");
    }
    startPartitionRule(e, t, i) {
      this.reportAndSkip("E_CSS_UNEXPECTED_PARTITION");
    }
    startPartitionGroupRule(e, t, i) {
      this.reportAndSkip("E_CSS_UNEXPECTED_PARTITION_GROUP");
    }
    startFuncWithSelector(e) {
      this.reportAndSkip("E_CSS_UNEXPECTED_SELECTOR_FUNC");
    }
    endFuncWithSelector() {
      this.reportAndSkip("E_CSS_UNEXPECTED_END_SELECTOR_FUNC");
    }
    property(e, t, i) {
      this.error("E_CSS_UNEXPECTED_PROPERTY", this.getCurrentToken());
    }
  },
  me = [],
  _r = [],
  re = [],
  Ee = [],
  Lt = [],
  dt = [],
  ye = [],
  Pe = [],
  se = [],
  Re = [],
  Ke = [],
  Le = [],
  xe = [],
  co = 55,
  uo = 56,
  Or = 57;
(me[1] = 28),
  (me[36] = 29),
  (me[7] = 29),
  (me[9] = 29),
  (me[14] = 29),
  (me[18] = 29),
  (me[50] = 29),
  (me[20] = 30),
  (me[13] = 27),
  (me[0] = 200),
  (_r[1] = 46),
  (_r[0] = 200),
  (dt[1] = 2),
  (dt[36] = 4),
  (dt[7] = 6),
  (dt[9] = 8),
  (dt[14] = 10),
  (dt[18] = 14),
  (dt[50] = 42),
  (re[37] = 11),
  (re[23] = 12),
  (re[35] = 56),
  (re[1] = 1),
  (re[36] = 3),
  (re[7] = 5),
  (re[9] = 7),
  (re[14] = 9),
  (re[12] = 13),
  (re[18] = 55),
  (re[50] = 58),
  (re[16] = 41),
  (Ee[37] = 11),
  (Ee[23] = 12),
  (Ee[35] = 56),
  (Ee[1] = 1),
  (Ee[36] = 3),
  (Ee[7] = 5),
  (Ee[9] = 7),
  (Ee[14] = 9),
  (Ee[18] = 55),
  (Lt[1] = 2),
  (Lt[36] = 4),
  (Lt[7] = 6),
  (Lt[9] = 8),
  (Lt[18] = 14),
  (Lt[50] = 42),
  (Lt[14] = 10),
  (Lt[12] = 13),
  (ye[1] = 15),
  (ye[7] = 16),
  (ye[4] = 17),
  (ye[5] = 18),
  (ye[3] = 19),
  (ye[2] = 20),
  (ye[8] = 21),
  (ye[27] = 57),
  (ye[16] = 22),
  (ye[19] = 23),
  (ye[6] = 24),
  (ye[11] = 25),
  (ye[17] = 26),
  (ye[13] = 48),
  (ye[31] = 47),
  (ye[23] = 54),
  (ye[0] = 44),
  (Pe[1] = 31),
  (Pe[4] = 32),
  (Pe[5] = 32),
  (Pe[3] = 33),
  (Pe[2] = 34),
  (Pe[10] = 40),
  (Pe[6] = 38),
  (Pe[31] = 36),
  (Pe[24] = 36),
  (Pe[32] = 35),
  (se[1] = 45),
  (se[16] = 37),
  (se[37] = 37),
  (se[38] = 37),
  (se[47] = 37),
  (se[48] = 37),
  (se[39] = 37),
  (se[49] = 37),
  (se[41] = 37),
  (se[26] = 37),
  (se[25] = 37),
  (se[23] = 37),
  (se[24] = 37),
  (se[19] = 37),
  (se[21] = 37),
  (se[36] = 37),
  (se[18] = 37),
  (se[22] = 37),
  (se[11] = 39),
  (se[12] = 43),
  (se[17] = 49),
  (Re[0] = 200),
  (Re[12] = 50),
  (Re[13] = 51),
  (Re[14] = 50),
  (Re[15] = 51),
  (Re[10] = 50),
  (Re[11] = 51),
  (Re[17] = 53),
  (Ke[0] = 200),
  (Ke[12] = 50),
  (Ke[13] = 52),
  (Ke[14] = 50),
  (Ke[15] = 51),
  (Ke[10] = 50),
  (Ke[11] = 51),
  (Ke[17] = 53),
  (Le[0] = 200),
  (Le[12] = 50),
  (Le[13] = 51),
  (Le[14] = 50),
  (Le[15] = 51),
  (Le[10] = 50),
  (Le[11] = 51),
  (xe[11] = 0),
  (xe[16] = 0),
  (xe[22] = 1),
  (xe[18] = 1),
  (xe[26] = 2),
  (xe[25] = 2),
  (xe[38] = 3),
  (xe[37] = 3),
  (xe[48] = 3),
  (xe[47] = 3),
  (xe[39] = 3),
  (xe[49] = 3),
  (xe[41] = 3),
  (xe[23] = 4),
  (xe[24] = 4),
  (xe[36] = 5),
  (xe[19] = 5),
  (xe[21] = 5),
  (xe[0] = 6),
  (xe[co] = 2),
  (xe[uo] = 2);
var Dn = class {
    constructor(e, t, i, n) {
      (this.actions = e),
        (this.tokenizer = t),
        (this.handler = i),
        (this.baseURL = n),
        p(this, "valStack", []),
        p(this, "namespacePrefixToURI", {}),
        p(this, "defaultNamespaceURI", null),
        p(this, "propName", null),
        p(this, "propImportant", !1),
        p(this, "exprContext"),
        p(this, "result", null),
        p(this, "importReady", !1),
        p(this, "importURL", null),
        p(this, "importCondition", null),
        p(this, "errorBrackets", []),
        p(this, "ruleStack", []),
        p(this, "regionRule", !1),
        p(this, "pageRule", !1),
        p(this, "inStyleDeclaration", !1),
        (this.exprContext = 2);
    }
    extractVals(e, t) {
      let i = [],
        n = this.valStack;
      for (; t < n.length && (i.push(n[t++]), t !== n.length); ) {
        if (n[t++] !== e) throw new Error("Unexpected state");
        t === n.length && i.push(O);
      }
      return i;
    }
    valStackReduce(e, t) {
      let i,
        n = this.valStack,
        r = n.length,
        s = 0;
      do {
        if (((i = n[--r]), ")" === e && i instanceof In))
          if (")" === i.text) s++;
          else if ("(" === i.text) {
            if (0 === s) return null;
            s--;
          }
      } while (void 0 !== i && "string" != typeof i);
      let o = n.length - (r + 1);
      if (
        (o > 1 && n.splice(r + 1, o, new q(n.slice(r + 1, n.length))), "," == e)
      )
        return null;
      r++;
      do {
        i = n[--r];
      } while (void 0 !== i && ("string" != typeof i || "," == i));
      if (((o = n.length - (r + 1)), "(" == i)) {
        ")" != e &&
          0 !== t.type &&
          (this.handler.error("E_CSS_MISMATCHED_C_PAR", t),
          (this.actions = Ke));
        let i = new At(n[r - 1], this.extractVals(",", r + 1));
        if ((n.splice(r - 1, o + 2, i), "var" === i.name)) {
          let e = i.values[0] instanceof be && i.values[0].name;
          (!bt(e) || e === this.propName) &&
            (this.handler.error(`E_CSS_INVALID_VAR ${i.toString()}`, t),
            (this.actions = Ke));
        }
        return i;
      }
      if (";" != e || r >= 0)
        return (
          this.handler.error("E_CSS_UNEXPECTED_VAL_END", t),
          (this.actions = Ke),
          null
        );
      if (o > 1) return new ge(this.extractVals(",", r + 1));
      let a = n[0];
      return a instanceof Se ? a : a ? new In(a.toString()) : O;
    }
    exprError(e, t) {
      (this.actions = this.propName ? Ke : Re), V.warn(e, t.toString());
    }
    exprStackReduce(e, t) {
      let i,
        n = this.valStack,
        r = this.handler,
        s = n.pop();
      for (;;) {
        let o = n.pop();
        if (11 == e) {
          let t = [s];
          for (; 16 == o; ) t.unshift(n.pop()), (o = n.pop());
          if ("string" == typeof o) {
            if ("{" == o) {
              for (; t.length >= 2; ) {
                let e = t.shift(),
                  i = t.shift(),
                  n = new mr(r.getScope(), e, i);
                t.unshift(n);
              }
              return n.push(new F(t[0])), !0;
            }
            if ("(" == o) {
              let i = n.pop(),
                o = n.pop();
              (s = new hn(r.getScope(), jo(o, i), t)), (e = 0);
              continue;
            }
          }
          if (10 == o) {
            s.isMediaName() && (s = new Xo(r.getScope(), s, null)), (e = 0);
            continue;
          }
        } else if ("string" == typeof o) {
          n.push(o);
          break;
        }
        if (o < 0)
          if (-31 == o) s = new mt(r.getScope(), s);
          else if (-24 == o) s = new Qt(r.getScope(), s);
          else {
            if (o != -Or) return this.exprError("F_UNEXPECTED_STATE", t), !1;
            s = new fr(r.getScope(), s);
          }
        else {
          if (xe[e] > xe[o]) {
            n.push(o);
            break;
          }
          switch (((i = n.pop()), o)) {
            case 26:
              s = new qs(r.getScope(), i, s);
              break;
            case co:
              s = new gr(r.getScope(), i, s);
              break;
            case uo:
              s = new Cr(r.getScope(), i, s);
              break;
            case 25:
              s = new Ks(r.getScope(), i, s);
              break;
            case 38:
              s = new br(r.getScope(), i, s);
              break;
            case 37:
              s = new yr(r.getScope(), i, s);
              break;
            case 48:
              s = new xr(r.getScope(), i, s);
              break;
            case 47:
              s = new ds(r.getScope(), i, s);
              break;
            case 39:
            case 49:
              s = new Rn(r.getScope(), i, s);
              break;
            case 41:
              s = new Er(r.getScope(), i, s);
              break;
            case 23:
              s = new zo(r.getScope(), i, s);
              break;
            case 24:
              s = new Go(r.getScope(), i, s);
              break;
            case 36:
              s = new ps(r.getScope(), i, s);
              break;
            case 19:
              s = new Wo(r.getScope(), i, s);
              break;
            case 21:
              s = new Zs(r.getScope(), i, s);
              break;
            case 18:
              if (!(n.length > 1))
                return this.exprError("E_CSS_EXPR_COND", t), !1;
              switch (n[n.length - 1]) {
                case 22:
                  n.pop(), (s = new Sr(r.getScope(), n.pop(), i, s));
                  break;
                case 10:
                  if (!i.isMediaName())
                    return this.exprError("E_CSS_MEDIA_TEST", t), !1;
                  s = new Xo(r.getScope(), i, s);
              }
              break;
            case 22:
              if (18 != e) return this.exprError("E_CSS_EXPR_COND", t), !1;
            case 10:
              return n.push(i), n.push(o), n.push(s), !1;
            default:
              return this.exprError("F_UNEXPECTED_STATE", t), !1;
          }
        }
      }
      return n.push(s), !1;
    }
    readSupportsTest(e) {
      let t,
        i,
        n = 6 === e.type,
        r = this.tokenizer;
      if (n) (i = e.text), (t = e.position + i.length + 1);
      else {
        if (10 !== e.type) return null;
        {
          let n = r.nthToken(1),
            s = r.nthToken(2);
          if (1 === n.type && 18 === s.type)
            r.consume(), r.consume(), (i = n.text), (t = s.position + 1);
          else {
            if (
              10 === n.type ||
              6 === n.type ||
              (1 === n.type &&
                "not" === n.text.toLowerCase() &&
                (10 === s.type || 6 === s.type))
            )
              return null;
            t = e.position + 1;
          }
        }
      }
      let s,
        o = 0,
        a = 0;
      for (; o >= 0; )
        switch ((r.consume(), (s = r.token()), s.type)) {
          case 11:
            o--;
            break;
          case 10:
          case 6:
            o++;
            break;
          case 16:
            0 === o && a++;
            break;
          case 0:
            return this.exprError("E_CSS_UNEXPECTED_EOF", s), null;
        }
      r.consume();
      let l = s.position,
        h =
          n && "selector" === i && a > 0 ? "" : r.input.substring(t, l).trim();
      return new Nr(this.handler.getScope(), i, h, n);
    }
    readPseudoParams() {
      let e = [];
      for (;;) {
        let t = this.tokenizer.token();
        switch (t.type) {
          case 1:
            e.push(t.text);
            break;
          case 23:
            e.push("+");
            break;
          case 4:
          case 5:
            e.push(t.num);
            break;
          default:
            return e;
        }
        this.tokenizer.consume();
      }
    }
    readNthPseudoParams() {
      let e = !1,
        t = this.tokenizer.token();
      if (23 === t.type)
        (e = !0), this.tokenizer.consume(), (t = this.tokenizer.token());
      else if (1 === t.type && ("even" === t.text || "odd" === t.text))
        return this.tokenizer.consume(), [2, "odd" === t.text ? 1 : 0];
      switch (t.type) {
        case 3:
          if (e && t.num < 0) return null;
        case 1:
          if (e && "-" === t.text.charAt(0)) return null;
          if ("n" === t.text || "-n" === t.text) {
            if (e && t.precededBySpace) return null;
            let i = "-n" === t.text ? -1 : 1;
            3 === t.type && (i = t.num);
            let n = 0;
            this.tokenizer.consume(), (t = this.tokenizer.token());
            let r = 24 === t.type,
              s = 23 === t.type || r;
            if (
              (s && (this.tokenizer.consume(), (t = this.tokenizer.token())),
              5 === t.type)
            ) {
              if (((n = t.num), 1 / n == -1 / 0)) {
                if (((n = 0), s)) return null;
              } else if (n < 0) {
                if (s) return null;
              } else if (n >= 0 && !s) return null;
              this.tokenizer.consume();
            } else if (s) return null;
            return [i, r && n > 0 ? -n : n];
          }
          if ("n-" === t.text || "-n-" === t.text) {
            if (e && t.precededBySpace) return null;
            let i = "-n-" === t.text ? -1 : 1;
            if (
              (3 === t.type && (i = t.num),
              this.tokenizer.consume(),
              (t = this.tokenizer.token()),
              5 === t.type)
            )
              return t.num < 0 || 1 / t.num == -1 / 0
                ? null
                : (this.tokenizer.consume(), [i, t.num]);
          } else {
            let i = t.text.match(/^n(-[0-9]+)$/);
            if (i)
              return e && t.precededBySpace
                ? null
                : (this.tokenizer.consume(),
                  [3 === t.type ? t.num : 1, parseInt(i[1], 10)]);
            if (((i = t.text.match(/^-n(-[0-9]+)$/)), i))
              return this.tokenizer.consume(), [-1, parseInt(i[1], 10)];
          }
          return null;
        case 5:
          return e && (t.precededBySpace || t.num < 0)
            ? null
            : (this.tokenizer.consume(), [0, t.num]);
      }
      return null;
    }
    makeCondition(e, t) {
      let i = this.handler.getScope();
      if (!i) return null;
      if (((t = t || i._true), e)) {
        let n = e.split(/\s+/);
        for (let e of n)
          switch (e) {
            case "vertical":
              t = tt(i, t, new mt(i, new ee(i, "pref-horizontal")));
              break;
            case "horizontal":
              t = tt(i, t, new ee(i, "pref-horizontal"));
              break;
            case "day":
              t = tt(i, t, new mt(i, new ee(i, "pref-night-mode")));
              break;
            case "night":
              t = tt(i, t, new ee(i, "pref-night-mode"));
              break;
            default:
              t = i._false;
          }
      }
      return t === i._true ? null : new F(t);
    }
    isInsidePropertyOnlyRule() {
      switch (this.ruleStack[this.ruleStack.length - 1]) {
        case "[selector]":
        case "font-face":
        case "-epubx-flow":
        case "-epubx-viewport":
        case "-epubx-define":
        case "-adapt-footnote-area":
          return !0;
      }
      return !1;
    }
    runParser(e, t, i, n, r, s) {
      let o,
        a,
        l,
        h,
        u,
        c,
        d,
        p = this.handler,
        f = this.tokenizer,
        g = this.valStack,
        m = null;
      for (
        i && (this.inStyleDeclaration = !0),
          n && ((this.exprContext = 2), this.valStack.push("{"));
        e > 0;
        --e
      )
        if (
          ((o = f.token()),
          r && null === m && ((m = o.position - 1), "(" === f.input[m] && m++),
          this.actions === ye &&
            this.errorBrackets.length > 0 &&
            (o.type === this.errorBrackets[this.errorBrackets.length - 1] ||
              17 === o.type ||
              31 === o.type))
        ) {
          if (
            o.type === this.errorBrackets[this.errorBrackets.length - 1] &&
            (this.errorBrackets.pop(),
            11 === o.type && this.valStackReduce(")", o))
          ) {
            f.consume();
            continue;
          }
          g.push(new In(o.toString())), f.consume();
        } else
          switch (this.actions[o.type]) {
            case 28:
              if (!this.inStyleDeclaration || 18 != f.nthToken(1).type) {
                this.isInsidePropertyOnlyRule()
                  ? (p.error("E_CSS_COLON_EXPECTED", f.nthToken(1)),
                    (this.actions = Ke))
                  : ((this.actions = dt), p.startSelectorRule());
                continue;
              }
              (this.propName = o.text),
                (this.propImportant = !1),
                f.consume(),
                f.consume(),
                (this.actions = ye),
                g.splice(0, g.length);
              continue;
            case 46:
              if (18 != f.nthToken(1).type) {
                (this.actions = Ke),
                  p.error("E_CSS_COLON_EXPECTED", f.nthToken(1));
                continue;
              }
              (this.propName = o.text),
                (this.propImportant = !1),
                f.consume(),
                f.consume(),
                (this.actions = ye),
                g.splice(0, g.length);
              continue;
            case 29:
              (this.actions = dt), p.startSelectorRule();
              continue;
            case 1:
              if (!o.precededBySpace) {
                (this.actions = Le), p.error("E_CSS_SPACE_EXPECTED", o);
                continue;
              }
              p.descendantSelector();
            case 2:
              if (34 == f.nthToken(1).type)
                if (
                  (f.consume(),
                  f.consume(),
                  (l = this.namespacePrefixToURI[o.text]),
                  null != l)
                )
                  switch (((o = f.token()), o.type)) {
                    case 1:
                      p.tagSelector(l, o.text),
                        (this.actions = r ? Ee : re),
                        f.consume();
                      break;
                    case 36:
                      p.tagSelector(l, null),
                        (this.actions = r ? Ee : re),
                        f.consume();
                      break;
                    default:
                      (this.actions = Re), p.error("E_CSS_NAMESPACE", o);
                  }
                else (this.actions = Re), p.error("E_CSS_UNDECLARED_PREFIX", o);
              else
                p.tagSelector(this.defaultNamespaceURI, o.text),
                  (this.actions = r ? Ee : re),
                  f.consume();
              continue;
            case 3:
              if (!o.precededBySpace) {
                (this.actions = Le), p.error("E_CSS_SPACE_EXPECTED", o);
                continue;
              }
              p.descendantSelector();
            case 4:
              if (34 == f.nthToken(1).type)
                switch ((f.consume(), f.consume(), (o = f.token()), o.type)) {
                  case 1:
                    p.tagSelector(null, o.text),
                      (this.actions = r ? Ee : re),
                      f.consume();
                    break;
                  case 36:
                    p.tagSelector(null, null),
                      (this.actions = r ? Ee : re),
                      f.consume();
                    break;
                  default:
                    (this.actions = Re), p.error("E_CSS_NAMESPACE", o);
                }
              else
                p.tagSelector(this.defaultNamespaceURI, null),
                  (this.actions = r ? Ee : re),
                  f.consume();
              continue;
            case 5:
              o.precededBySpace && p.descendantSelector();
            case 6:
              if (!o.text) {
                p.error("E_CSS_SYNTAX", o), f.consume();
                continue;
              }
              p.idSelector(o.text), (this.actions = r ? Ee : re), f.consume();
              continue;
            case 7:
              o.precededBySpace && p.descendantSelector();
            case 8:
              p.classSelector(o.text),
                (this.actions = r ? Ee : re),
                f.consume();
              continue;
            case 55:
              o.precededBySpace && p.descendantSelector();
            case 14:
              f.consume(), (o = f.token());
              e: switch (o.type) {
                case 1:
                  p.pseudoclassSelector(o.text, null),
                    f.consume(),
                    (this.actions = r ? Ee : re);
                  continue;
                case 6:
                  switch (((h = o.text), f.consume(), h)) {
                    case "is":
                    case "not":
                    case "where":
                    case "has":
                      (this.actions = dt),
                        p.startFuncWithSelector(h),
                        this.runParser(
                          Number.POSITIVE_INFINITY,
                          !1,
                          !1,
                          !1,
                          !0,
                          "has" === h
                        )
                          ? (this.actions = re)
                          : (this.actions = Le);
                      continue;
                    case "lang":
                    case "href-epub-type":
                      if (((o = f.token()), 1 === o.type)) {
                        (d = [o.text]),
                          f.consume(),
                          "href-epub-type" === h &&
                            16 === f.token().type &&
                            (f.consume(),
                            (o = f.token()),
                            1 === o.type && (d.push(o.text), f.consume()));
                        break;
                      }
                      break e;
                    case "nth-child":
                    case "nth-of-type":
                    case "nth-last-child":
                    case "nth-last-of-type":
                    case "nth":
                      if (((d = this.readNthPseudoParams()), d)) break;
                      break e;
                    default:
                      d = this.readPseudoParams();
                  }
                  if (((o = f.token()), 11 == o.type)) {
                    p.pseudoclassSelector(h, d),
                      f.consume(),
                      (this.actions = r ? Ee : re);
                    continue;
                  }
              }
              p.error("E_CSS_PSEUDOCLASS_SYNTAX", o), (this.actions = Re);
              continue;
            case 58:
              o.precededBySpace && p.descendantSelector();
            case 42:
              switch ((f.consume(), (o = f.token()), o.type)) {
                case 1:
                  p.pseudoelementSelector(o.text, null),
                    (this.actions = r ? Ee : re),
                    f.consume();
                  continue;
                case 6:
                  if (((h = o.text), f.consume(), "nth-fragment" == h)) {
                    if (((d = this.readNthPseudoParams()), null === d)) break;
                  } else d = this.readPseudoParams();
                  if (((o = f.token()), 11 == o.type)) {
                    p.pseudoelementSelector(h, d),
                      (this.actions = r ? Ee : re),
                      f.consume();
                    continue;
                  }
              }
              p.error("E_CSS_PSEUDOELEM_SYNTAX", o), (this.actions = Re);
              continue;
            case 9:
              o.precededBySpace && p.descendantSelector();
            case 10:
              if ((f.consume(), (o = f.token()), 1 == o.type))
                (h = o.text), f.consume();
              else if (36 == o.type) (h = null), f.consume();
              else {
                if (34 != o.type) {
                  (this.actions = Le), p.error("E_CSS_ATTR", o), f.consume();
                  continue;
                }
                h = "";
              }
              if (((o = f.token()), 34 == o.type)) {
                if (((l = h && this.namespacePrefixToURI[h]), void 0 === l)) {
                  (this.actions = Le),
                    p.error("E_CSS_UNDECLARED_PREFIX", o),
                    f.consume();
                  continue;
                }
                if ((f.consume(), (o = f.token()), 1 != o.type)) {
                  (this.actions = Le), p.error("E_CSS_ATTR_NAME_EXPECTED", o);
                  continue;
                }
                (h = o.text), f.consume(), (o = f.token());
              } else l = "";
              switch (o.type) {
                case 39:
                case 45:
                case 44:
                case 43:
                case 42:
                case 46:
                case 50:
                  (u = o.type), f.consume(), (o = f.token());
                  break;
                case 15:
                  p.attributeSelector(l, h, 0, null),
                    (this.actions = r ? Ee : re),
                    f.consume();
                  continue;
                default:
                  (this.actions = Le), p.error("E_CSS_ATTR_OP_EXPECTED", o);
                  continue;
              }
              switch (o.type) {
                case 1:
                case 2:
                  p.attributeSelector(l, h, u, o.text),
                    f.consume(),
                    (o = f.token());
                  break;
                default:
                  (this.actions = Le), p.error("E_CSS_ATTR_VAL_EXPECTED", o);
                  continue;
              }
              if (15 != o.type) {
                (this.actions = Le), p.error("E_CSS_ATTR", o);
                continue;
              }
              (this.actions = r ? Ee : re), f.consume();
              continue;
            case 11:
              p.childSelector(), (this.actions = Lt), f.consume();
              continue;
            case 12:
              p.adjacentSiblingSelector(), (this.actions = Lt), f.consume();
              continue;
            case 56:
              p.followingSiblingSelector(), (this.actions = Lt), f.consume();
              continue;
            case 13:
              this.regionRule
                ? (this.ruleStack.push("-epubx-region"), (this.regionRule = !1))
                : this.pageRule
                ? (this.ruleStack.push("page"),
                  (this.pageRule = !1),
                  (this.inStyleDeclaration = !0))
                : (this.ruleStack.push("[selector]"),
                  (this.inStyleDeclaration = !0)),
                p.startRuleBody(),
                (this.actions = me),
                f.consume();
              continue;
            case 41:
              p.nextSelector(), (this.actions = dt), f.consume();
              continue;
            case 15:
              g.push(L(o.text)), f.consume();
              continue;
            case 16:
              (u = parseInt(o.text, 16)), g.push(new eo(o.text)), f.consume();
              continue;
            case 17:
              g.push(new nt(o.num)), f.consume();
              continue;
            case 18:
              g.push(new ut(o.num)), f.consume();
              continue;
            case 19:
              g.push(new P(o.num, o.text)), f.consume();
              continue;
            case 20:
              g.push(new ue(o.text)), f.consume();
              continue;
            case 21:
              g.push(new Oe(J(o.text, this.baseURL))), f.consume();
              continue;
            case 57:
              g.push(new qo(o.text)), f.consume();
              continue;
            case 22:
              this.valStackReduce(",", o), g.push(","), f.consume();
              continue;
            case 23:
              g.push(to), f.consume();
              continue;
            case 24:
              (h = o.text.toLowerCase()),
                "-epubx-expr" == h || "env" == h
                  ? ((this.actions = Pe), (this.exprContext = 0), g.push("{"))
                  : (g.push(h),
                    g.push("("),
                    this.errorBrackets.length > 0 &&
                      this.errorBrackets.push(11)),
                f.consume();
              continue;
            case 25:
              this.valStackReduce(")", o), f.consume();
              continue;
            case 47:
              if (
                (f.consume(),
                (o = f.token()),
                (a = f.nthToken(1)),
                1 == o.type &&
                  "important" == o.text.toLowerCase() &&
                  (17 == a.type || 0 == a.type || 13 == a.type))
              ) {
                f.consume(), (this.propImportant = !0);
                continue;
              }
              this.exprError("E_CSS_SYNTAX", o);
              continue;
            case 54:
              switch (((a = f.nthToken(1)), a.type)) {
                case 4:
                case 3:
                case 5:
                  if (!a.precededBySpace) {
                    f.consume();
                    continue;
                  }
              }
              g.push(new In("+")), f.consume();
              continue;
            case 26:
              f.consume();
            case 48:
              (c = this.valStackReduce(";", o)),
                c &&
                  this.propName &&
                  p.property(this.propName, c, this.propImportant),
                (this.actions = i ? _r : me);
              continue;
            case 44:
              for (f.consume(); g.length > 0; ) {
                let e = g.length;
                if (((c = this.valStackReduce(";", o)), !c || g.length === e))
                  break;
              }
              return t
                ? ((this.result = c), !0)
                : (this.propName &&
                    c &&
                    p.property(this.propName, c, this.propImportant),
                  !0);
            case 31:
              if (((a = f.nthToken(1)), 9 == a.type))
                10 != f.nthToken(2).type || f.nthToken(2).precededBySpace
                  ? (g.push(new ee(p.getScope(), jo(o.text, a.text))),
                    (this.actions = se))
                  : (g.push(o.text, a.text, "("), f.consume()),
                  f.consume();
              else {
                if (2 == this.exprContext || 3 == this.exprContext)
                  "not" == o.text.toLowerCase()
                    ? (f.consume(), g.push(new $o(p.getScope(), !0, a.text)))
                    : ("only" == o.text.toLowerCase() && (f.consume(), (o = a)),
                      g.push(new $o(p.getScope(), !1, o.text)));
                else {
                  if (
                    4 === this.exprContext &&
                    "not" === o.text.toLowerCase() &&
                    g[g.length - 1] !== co &&
                    g[g.length - 1] !== uo &&
                    (10 === a.type || 6 === a.type)
                  ) {
                    g.push(-Or), f.consume();
                    continue;
                  }
                  g.push(new ee(p.getScope(), o.text));
                }
                this.actions = se;
              }
              f.consume();
              continue;
            case 38:
              if (4 === this.exprContext) {
                g.push(this.readSupportsTest(o)), (this.actions = se);
                continue;
              }
              g.push(null, o.text, "("), f.consume();
              continue;
            case 32:
              g.push(new ie(p.getScope(), o.num)),
                f.consume(),
                (this.actions = se);
              continue;
            case 33:
              (h = o.text),
                "%" == h &&
                  (h =
                    this.propName &&
                    this.propName.match(/height|^(top|bottom)$/)
                      ? "vh"
                      : "vw"),
                g.push(new Ot(p.getScope(), o.num, h)),
                f.consume(),
                (this.actions = se);
              continue;
            case 34:
              g.push(new ie(p.getScope(), o.text)),
                f.consume(),
                (this.actions = se);
              continue;
            case 35:
              f.consume(),
                (o = f.token()),
                5 != o.type || o.precededBySpace
                  ? this.exprError("E_CSS_SYNTAX", o)
                  : (g.push(new Qs(p.getScope(), o.num)),
                    f.consume(),
                    (this.actions = se));
              continue;
            case 36:
              g.push(-o.type), f.consume();
              continue;
            case 37:
              (this.actions = Pe),
                this.exprStackReduce(o.type, o),
                g.push(o.type),
                f.consume();
              continue;
            case 45:
              "and" === o.text.toLowerCase() &&
              g[g.length - 2] !== uo &&
              g[g.length - 2] !== -Or
                ? ((this.actions = Pe),
                  this.exprStackReduce(co, o),
                  g.push(co),
                  f.consume())
                : "or" === o.text.toLowerCase() &&
                  g[g.length - 2] !== co &&
                  g[g.length - 2] !== -Or
                ? ((this.actions = Pe),
                  this.exprStackReduce(uo, o),
                  g.push(uo),
                  f.consume())
                : this.exprError("E_CSS_SYNTAX", o);
              continue;
            case 39:
              this.exprStackReduce(o.type, o) && (this.actions = ye),
                f.consume();
              continue;
            case 43:
              this.exprStackReduce(11, o) &&
                (this.propName || 3 == this.exprContext
                  ? this.exprError("E_CSS_UNEXPECTED_BRC", o)
                  : (1 == this.exprContext
                      ? p.startWhenRule(g.pop())
                      : p.startMediaRule(g.pop()),
                    this.ruleStack.push("media"),
                    p.startRuleBody(),
                    (this.actions = me))),
                f.consume();
              continue;
            case 49:
              if (this.exprStackReduce(11, o))
                return this.propName || 3 != this.exprContext
                  ? (this.exprError("E_CSS_UNEXPECTED_SEMICOL", o),
                    (this.actions = me),
                    f.consume(),
                    !1)
                  : ((this.importCondition = g.pop()),
                    (this.importReady = !0),
                    (this.actions = me),
                    f.consume(),
                    !1);
              f.consume();
              continue;
            case 40:
              if (4 === this.exprContext) {
                let e = this.readSupportsTest(o);
                if (e) {
                  g.push(e), (this.actions = se);
                  continue;
                }
              }
              g.push(o.type), f.consume();
              continue;
            case 27:
              if (
                ((this.actions = me),
                f.consume(),
                p.endRule(),
                (this.inStyleDeclaration = !1),
                this.ruleStack.length)
              )
                switch (
                  (this.ruleStack.pop(),
                  this.ruleStack[this.ruleStack.length - 1])
                ) {
                  case "page":
                  case "-epubx-page-master":
                  case "-epubx-partition-group":
                    this.inStyleDeclaration = !0;
                }
              continue;
            case 30:
              switch (((h = o.text.toLowerCase()), h)) {
                case "import":
                  if (
                    (f.consume(), (o = f.token()), 2 == o.type || 8 == o.type)
                  ) {
                    if (
                      ((this.importURL = o.text),
                      f.consume(),
                      (o = f.token()),
                      17 == o.type || 0 == o.type)
                    )
                      return (this.importReady = !0), f.consume(), !1;
                    (this.propName = null),
                      (this.exprContext = 3),
                      (this.actions = Pe),
                      g.push("{");
                    continue;
                  }
                  p.error("E_CSS_IMPORT_SYNTAX", o), (this.actions = Re);
                  continue;
                case "namespace":
                  switch ((f.consume(), (o = f.token()), o.type)) {
                    case 1:
                      if (
                        ((h = o.text),
                        f.consume(),
                        (o = f.token()),
                        (2 == o.type || 8 == o.type) &&
                          17 == f.nthToken(1).type)
                      ) {
                        (this.namespacePrefixToURI[h] = o.text),
                          f.consume(),
                          f.consume();
                        continue;
                      }
                      break;
                    case 2:
                    case 8:
                      if (17 == f.nthToken(1).type) {
                        (this.defaultNamespaceURI = o.text),
                          f.consume(),
                          f.consume();
                        continue;
                      }
                  }
                  p.error("E_CSS_NAMESPACE_SYNTAX", o), (this.actions = Re);
                  continue;
                case "charset":
                  if (
                    (f.consume(),
                    (o = f.token()),
                    2 == o.type && 17 == f.nthToken(1).type)
                  ) {
                    (h = o.text.toLowerCase()),
                      "utf-8" != h &&
                        "utf-16" != h &&
                        p.error(`E_CSS_UNEXPECTED_CHARSET ${h}`, o),
                      f.consume(),
                      f.consume();
                    continue;
                  }
                  p.error("E_CSS_CHARSET_SYNTAX", o), (this.actions = Re);
                  continue;
                case "font-face":
                case "-epubx-page-template":
                case "-epubx-define":
                case "-epubx-viewport":
                  if (12 == f.nthToken(1).type) {
                    switch ((f.consume(), f.consume(), h)) {
                      case "font-face":
                        p.startFontFaceRule(), (this.inStyleDeclaration = !0);
                        break;
                      case "-epubx-page-template":
                        p.startPageTemplateRule();
                        break;
                      case "-epubx-define":
                        p.startDefineRule(), (this.inStyleDeclaration = !0);
                        break;
                      case "-epubx-viewport":
                        p.startViewportRule(), (this.inStyleDeclaration = !0);
                    }
                    this.ruleStack.push(h), p.startRuleBody();
                    continue;
                  }
                  break;
                case "-adapt-footnote-area":
                  switch ((f.consume(), (o = f.token()), o.type)) {
                    case 12:
                      f.consume(),
                        p.startFootnoteRule(null),
                        this.ruleStack.push(h),
                        p.startRuleBody(),
                        (this.inStyleDeclaration = !0);
                      continue;
                    case 50:
                      if (
                        (f.consume(),
                        (o = f.token()),
                        1 == o.type && 12 == f.nthToken(1).type)
                      ) {
                        (h = o.text),
                          f.consume(),
                          f.consume(),
                          p.startFootnoteRule(h),
                          this.ruleStack.push("-adapt-footnote-area"),
                          p.startRuleBody(),
                          (this.inStyleDeclaration = !0);
                        continue;
                      }
                  }
                  break;
                case "-epubx-region":
                  f.consume(),
                    p.startRegionRule(),
                    (this.regionRule = !0),
                    (this.actions = dt);
                  continue;
                case "page":
                  f.consume(),
                    p.startPageRule(),
                    (this.pageRule = !0),
                    (this.actions = Lt);
                  continue;
                case "top-left-corner":
                case "top-left":
                case "top-center":
                case "top-right":
                case "top-right-corner":
                case "right-top":
                case "right-middle":
                case "right-bottom":
                case "bottom-right-corner":
                case "bottom-right":
                case "bottom-center":
                case "bottom-left":
                case "bottom-left-corner":
                case "left-bottom":
                case "left-middle":
                case "left-top":
                  if ((f.consume(), (o = f.token()), 12 == o.type)) {
                    f.consume(),
                      p.startPageMarginBoxRule(h),
                      this.ruleStack.push(h),
                      p.startRuleBody(),
                      (this.inStyleDeclaration = !0);
                    continue;
                  }
                  break;
                case "-epubx-when":
                  f.consume(),
                    (this.propName = null),
                    (this.exprContext = 1),
                    (this.actions = Pe),
                    g.push("{");
                  continue;
                case "media":
                  f.consume(),
                    (this.propName = null),
                    (this.exprContext = 2),
                    (this.actions = Pe),
                    g.push("{");
                  continue;
                case "supports":
                  f.consume(),
                    (this.propName = null),
                    (this.exprContext = 4),
                    (this.actions = Pe),
                    g.push("{");
                  continue;
                case "-epubx-flow":
                  if (1 == f.nthToken(1).type && 12 == f.nthToken(2).type) {
                    p.startFlowRule(f.nthToken(1).text),
                      f.consume(),
                      f.consume(),
                      f.consume(),
                      this.ruleStack.push(h),
                      p.startRuleBody(),
                      (this.inStyleDeclaration = !0);
                    continue;
                  }
                  break;
                case "-epubx-page-master":
                case "-epubx-partition":
                case "-epubx-partition-group": {
                  f.consume(), (o = f.token());
                  let e = null,
                    t = null,
                    i = [];
                  for (
                    1 == o.type && ((e = o.text), f.consume(), (o = f.token())),
                      18 == o.type &&
                        1 == f.nthToken(1).type &&
                        ((t = f.nthToken(1).text),
                        f.consume(),
                        f.consume(),
                        (o = f.token()));
                    6 == o.type &&
                    "class" == o.text.toLowerCase() &&
                    1 == f.nthToken(1).type &&
                    11 == f.nthToken(2).type;

                  )
                    i.push(f.nthToken(1).text),
                      f.consume(),
                      f.consume(),
                      f.consume(),
                      (o = f.token());
                  if (12 == o.type) {
                    switch ((f.consume(), h)) {
                      case "-epubx-page-master":
                        p.startPageMasterRule(e, t, i);
                        break;
                      case "-epubx-partition":
                        p.startPartitionRule(e, t, i);
                        break;
                      case "-epubx-partition-group":
                        p.startPartitionGroupRule(e, t, i);
                    }
                    this.ruleStack.push(h),
                      p.startRuleBody(),
                      (this.inStyleDeclaration = !0);
                    continue;
                  }
                  break;
                }
                case "":
                  p.error(`E_CSS_UNEXPECTED_AT${h}`, o), (this.actions = Le);
                  continue;
                default:
                  p.error(`E_CSS_AT_UNKNOWN ${h}`, o), (this.actions = Re);
                  continue;
              }
              p.error(`E_CSS_AT_SYNTAX ${h}`, o), (this.actions = Re);
              continue;
            case 50:
              this.errorBrackets.push(o.type + 1), f.consume();
              continue;
            case 52:
              if (0 == this.errorBrackets.length) {
                this.actions = me;
                continue;
              }
            case 51:
              if (r && 0 == this.errorBrackets.length && 11 == o.type)
                return f.consume(), p.endFuncWithSelector(), !0;
              this.errorBrackets.length > 0 &&
                this.errorBrackets[this.errorBrackets.length - 1] == o.type &&
                this.errorBrackets.pop(),
                0 == this.errorBrackets.length &&
                  13 == o.type &&
                  (this.actions = me),
                f.consume();
              continue;
            case 53:
              0 == this.errorBrackets.length && (this.actions = me),
                f.consume();
              continue;
            case 200:
              return !0;
            default:
              if (n)
                return (
                  !!this.exprStackReduce(11, o) && ((this.result = g.pop()), !0)
                );
              if (r) {
                switch (o.type) {
                  case 16:
                  case 11:
                    if (this.actions === dt) p.error("E_CSS_SYNTAX", o);
                    else {
                      let e = f.input.substring(m, o.position);
                      p.pushSelectorText(e), (m = o.position + 1);
                    }
                    if (16 === o.type) {
                      p.nextSelector(), (this.actions = dt), f.consume();
                      continue;
                    }
                    return p.endFuncWithSelector(), f.consume(), !0;
                  case 37:
                  case 23:
                  case 35:
                    if (s) {
                      this.actions = re;
                      continue;
                    }
                    break;
                  case 12:
                  case 14:
                  case 10:
                    this.errorBrackets.push(o.type + 1);
                }
                p.error("E_CSS_SYNTAX", o), f.consume(), (this.actions = Le);
                continue;
              }
              if (
                this.actions !== Re &&
                this.actions !== Le &&
                this.actions !== Ke
              ) {
                if (54 == o.type) p.error("E_CSS_SYNTAX", o);
                else {
                  if (this.actions === ye) {
                    switch (o.type) {
                      case 10:
                      case 12:
                      case 14:
                        this.errorBrackets.push(o.type + 1);
                    }
                    g.push(new In(o.toString())), f.consume();
                    continue;
                  }
                  if (12 === o.type && this.actions == Pe && g.length > 0) {
                    p.startMediaRule(g.pop()),
                      this.ruleStack.push("media"),
                      p.startRuleBody(),
                      (this.actions = me),
                      f.consume();
                    continue;
                  }
                  if (17 === o.type && this.actions == Pe)
                    return (this.actions = me), f.consume(), !1;
                  p.error("E_CSS_SYNTAX", o);
                }
                this.isInsidePropertyOnlyRule()
                  ? (this.actions = Ke)
                  : (this.actions = Le);
                continue;
              }
              f.consume();
              continue;
          }
      return !1;
    }
  },
  ti = class extends ei {
    constructor(e) {
      super(null), (this.scope = e);
    }
    error(e, t) {
      V.warn(e, t.toString());
    }
    getScope() {
      return this.scope;
    }
  };
function Gm(e, t, i, n, r) {
  let s = A("parseStylesheet"),
    o = new Dn(me, e, t, i),
    a = null;
  return (
    r && (a = Wm(new De(r, t), t, i)),
    (a = o.makeCondition(n, a && a.toExpr())),
    a && (t.startMediaRule(a), t.startRuleBody()),
    s
      .loop(() => {
        for (; !o.runParser(100, !1, !1, !1, !1); ) {
          if (o.importReady) {
            let e = J(o.importURL, i);
            o.importCondition &&
              (t.startMediaRule(o.importCondition), t.startRuleBody());
            let n = A("parseStylesheet.import");
            return (
              wc(e, t, null, null).then(() => {
                o.importCondition && t.endRule(),
                  (o.importReady = !1),
                  (o.importURL = null),
                  (o.importCondition = null),
                  n.finish(!0);
              }),
              n.result()
            );
          }
          let e = s.timeSlice();
          if (e.isPending) return e;
        }
        return T(!1);
      })
      .then(() => {
        a && t.endRule(), s.finish(!0);
      }),
    s.result()
  );
}
function zr(e, t, i, n, r) {
  return gn(
    "parseStylesheetFromText",
    (s) => {
      Gm(new De(e, t), t, i, n, r).thenFinish(s);
    },
    (t, i) => {
      V.warn(i, `Failed to parse stylesheet text: ${e}`), t.finish(!1);
    }
  );
}
function wc(e, t, i, n) {
  return gn(
    "parseStylesheetFromURL",
    (r) => {
      bs(e).then((s) => {
        s.responseText
          ? zr(s.responseText, t, e, i, n).then((t) => {
              t || V.warn(`Failed to parse stylesheet from ${e}`), r.finish(!0);
            })
          : r.finish(!0);
      });
    },
    (t, i) => {
      V.warn(i, "Exception while fetching and parsing:", e), t.finish(!0);
    }
  );
}
function sn(e, t, i) {
  let n = new Dn(ye, t, new ti(e), i);
  return n.runParser(Number.POSITIVE_INFINITY, !0, !1, !1, !1), n.result;
}
function Nh(e, t, i) {
  new Dn(_r, e, t, i).runParser(Number.POSITIVE_INFINITY, !1, !0, !1, !1);
}
function Wm(e, t, i) {
  let n = new Dn(Pe, e, t, i);
  return n.runParser(Number.POSITIVE_INFINITY, !1, !1, !0, !1), n.result;
}
var $m = {
  "z-index": !0,
  "column-count": !0,
  "flow-linger": !0,
  opacity: !0,
  page: !0,
  "flow-priority": !0,
  utilization: !0,
};
function Xm(e) {
  return !!$m[e];
}
function vh(e, t, i) {
  if (t instanceof Z && "viv-leader" === t.str) return new F(t);
  let n = t.evaluate(e);
  switch (typeof n) {
    case "number":
      return Xm(i)
        ? n == Math.round(n)
          ? new ut(n)
          : new nt(n)
        : new P(n, "px");
    case "string":
      return n ? sn(t.scope, new De(n, null), "") : O;
    case "boolean":
      return n ? b._true : b._false;
    case "undefined":
      return O;
  }
  throw new Error("E_UNEXPECTED");
}
function Th(e, t) {
  let i = {};
  return (
    Object.keys(e).forEach((n) => {
      let r = (i[n] = {}),
        s = e[n];
      Object.keys(s).forEach((e) => {
        r[e] = s[e].map((e) => {
          let i = t ? e.logical : e.physical,
            n = t ? e.physical : e.logical;
          return { regexp: new RegExp(`(-?)${i}(-?)`), to: `$1${n}$2` };
        });
      });
    }),
    i
  );
}
function wh(e, t, i, n) {
  let r = n[t];
  if (!r) throw new Error(`unknown writing-mode: ${t}`);
  let s = r[i || "ltr"];
  if (!s) throw new Error(`unknown direction: ${i}`);
  for (let t of s) {
    let i = e.replace(t.regexp, t.to);
    if (i !== e) return i;
  }
  return e;
}
var Ph = {
    "horizontal-tb": {
      ltr: [
        { logical: "inline-start", physical: "left" },
        { logical: "inline-end", physical: "right" },
        { logical: "block-start", physical: "top" },
        { logical: "block-end", physical: "bottom" },
        { logical: "inline-size", physical: "width" },
        { logical: "block-size", physical: "height" },
      ],
      rtl: [
        { logical: "inline-start", physical: "right" },
        { logical: "inline-end", physical: "left" },
        { logical: "block-start", physical: "top" },
        { logical: "block-end", physical: "bottom" },
        { logical: "inline-size", physical: "width" },
        { logical: "block-size", physical: "height" },
      ],
    },
    "vertical-rl": {
      ltr: [
        { logical: "inline-start", physical: "top" },
        { logical: "inline-end", physical: "bottom" },
        { logical: "block-start", physical: "right" },
        { logical: "block-end", physical: "left" },
        { logical: "inline-size", physical: "height" },
        { logical: "block-size", physical: "width" },
      ],
      rtl: [
        { logical: "inline-start", physical: "bottom" },
        { logical: "inline-end", physical: "top" },
        { logical: "block-start", physical: "right" },
        { logical: "block-end", physical: "left" },
        { logical: "inline-size", physical: "height" },
        { logical: "block-size", physical: "width" },
      ],
    },
    "vertical-lr": {
      ltr: [
        { logical: "inline-start", physical: "top" },
        { logical: "inline-end", physical: "bottom" },
        { logical: "block-start", physical: "left" },
        { logical: "block-end", physical: "right" },
        { logical: "inline-size", physical: "height" },
        { logical: "block-size", physical: "width" },
      ],
      rtl: [
        { logical: "inline-start", physical: "bottom" },
        { logical: "inline-end", physical: "top" },
        { logical: "block-start", physical: "left" },
        { logical: "block-end", physical: "right" },
        { logical: "inline-size", physical: "height" },
        { logical: "block-size", physical: "width" },
      ],
    },
  },
  jm = Th(Ph, !0);
function Pc(e, t, i) {
  return wh(e, t, i || null, jm);
}
var Ym = Th(Ph, !1);
function kc(e, t, i) {
  return wh(e, t, i || null, Ym);
}
var qm = {
  "horizontal-tb": [
    { logical: "line-left", physical: "left" },
    { logical: "line-right", physical: "right" },
    { logical: "over", physical: "top" },
    { logical: "under", physical: "bottom" },
  ],
  "vertical-rl": [
    { logical: "line-left", physical: "top" },
    { logical: "line-right", physical: "bottom" },
    { logical: "over", physical: "right" },
    { logical: "under", physical: "left" },
  ],
  "vertical-lr": [
    { logical: "line-left", physical: "top" },
    { logical: "line-right", physical: "bottom" },
    { logical: "over", physical: "right" },
    { logical: "under", physical: "left" },
  ],
};
function kh(e, t) {
  let i = qm[t];
  if (!i) throw new Error(`unknown writing-mode: ${t}`);
  for (let t = 0; t < i.length; t++)
    if (i[t].physical === e) return i[t].logical;
  return e;
}
function po(e, t, i) {
  var n;
  let r = t,
    s = { width: r.style.width, height: r.style.height };
  function o(i) {
    return e.getElementComputedStyle(t).getPropertyValue(i);
  }
  let a = "horizontal-tb" !== o("writing-mode"),
    l = a ? "height" : "width",
    h = a ? "width" : "height";
  function u(e, t) {
    r.style[e] = t;
    let i = o(e);
    return (r.style[e] = s[e]), i;
  }
  let c = {};
  for (let e of i) {
    let i;
    switch (e) {
      case "max-content inline size":
        i = u(l, "max-content");
        break;
      case "min-content inline size":
        i = u(l, "min-content");
        break;
      case "fit-content inline size":
        i = u(l, "fit-content");
        break;
      case "max-content block size":
        i = u(h, "max-content");
        break;
      case "min-content block size":
        i = u(h, "min-content");
        break;
      case "fit-content block size":
        i = u(h, "fit-content");
        break;
      case "max-content width":
        i = u("width", "max-content");
        break;
      case "max-content height":
        i = u("height", "max-content");
        break;
      case "min-content width":
        i = u("width", "min-content");
        break;
      case "min-content height":
        i = u("height", "min-content");
        break;
      case "fit-content width":
        i = u("width", "fit-content");
        break;
      case "fit-content height":
        i = u("height", "fit-content");
    }
    "0px" === i &&
      1 === t.childNodes.length &&
      "img" === (null == (n = t.firstElementChild) ? void 0 : n.localName) &&
      !t.firstElementChild.complete &&
      (i = "1px"),
      (c[e] = parseFloat(i));
  }
  return c;
}
function Es(e) {
  var t, i;
  return (
    "clone" ===
      (null == (t = null == e ? void 0 : e.style)
        ? void 0
        : t["box-decoration-break"]) ||
    "clone" ===
      (null == (i = null == e ? void 0 : e.style)
        ? void 0
        : i["-webkit-box-decoration-break"])
  );
}
function Lc(e) {
  let t = e.getAttribute("data-viv-box-break");
  return t ? t.split(" ") : [];
}
function Rc(e, t) {
  e.setAttribute("data-viv-box-break", t.join(" "));
}
function pt(e, t) {
  let i = Lc(e);
  i.includes(t) || (i.push(t), Rc(e, i));
}
function Zm(e) {
  let t = e.getAttribute("data-viv-margin-discard");
  return t ? t.split(" ") : [];
}
function Qm(e, t) {
  e.setAttribute("data-viv-margin-discard", t.join(" "));
}
function Gr(e, t) {
  let i = Zm(e);
  i.includes(t) || (i.push(t), Qm(e, i));
}
function Jm(e) {
  let t = e.name,
    i = e.value;
  switch (t) {
    case "page-break-before":
    case "page-break-after":
    case "page-break-inside":
      return {
        name: t.replace(/^page-/, ""),
        value: i === b.always ? b.page : i,
        important: e.important,
      };
    default:
      return e;
  }
}
var Wr = {
  page: !0,
  left: !0,
  right: !0,
  recto: !0,
  verso: !0,
  column: !0,
  region: !0,
};
function Ie(e) {
  return !!Wr[e];
}
var eC = { left: !0, right: !0, recto: !0, verso: !0 };
function Mn(e) {
  return !!eC[e];
}
var tC = {
  avoid: !0,
  "avoid-page": !0,
  "avoid-column": !0,
  "avoid-region": !0,
};
function ho(e) {
  return !!tC[e];
}
function Ve(e, t) {
  if (!e) return t;
  if (!t) return e;
  if (Mn(t)) return t;
  if (Mn(e)) return e;
  {
    let i = Ie(e),
      n = Ie(t);
    if (!i || !n) return n ? t : i ? e : ho(t) ? t : ho(e) ? e : t;
    switch (t) {
      case "column":
        return e;
      case "region":
        return "column" === e ? t : e;
      default:
        return t;
    }
  }
}
function Ic(e) {
  return Ie(e) ? e : "auto";
}
Ue("SIMPLE_PROPERTY", Jm);
var jr,
  _n,
  si,
  on,
  _t,
  Rt,
  fo = Xg(_h());
function Uh(e, t) {
  return (0, fo.default)(e, t, 0);
}
function Oc(e) {
  return e.reduce((e, t) => (t[0] === fo.default.DELETE ? e : e + t[1]), "");
}
function Hh(e, t) {
  return Gh(e, t, 1);
}
function zh(e, t) {
  return Gh(e, t, -1);
}
function Gh(e, t, i) {
  let n = 0,
    r = 0;
  return (
    e.some((e) => {
      for (let s = 0; s < e[1].length; s++) {
        switch (e[0] * i) {
          case fo.default.INSERT:
            n++;
            break;
          case fo.default.DELETE:
            n--, r++;
            break;
          case fo.default.EQUAL:
            r++;
        }
        if (r > t) return !0;
      }
      return !1;
    }),
    Math.max(Math.min(t, r - 1) + n, 0)
  );
}
((jr || (jr = {})).isInstanceOfBlockFormattingContext = function (e) {
  return e && "Block" === e.formattingContextType;
}),
  ((e) => {
    let t;
    var i;
    ((i = t = e.FloatReference || (e.FloatReference = {})).INLINE = "inline"),
      (i.COLUMN = "column"),
      (i.REGION = "region"),
      (i.PAGE = "page");
  })(_n || (_n = {})),
  ((si || (si = {})).isInstanceOfAfterIfContinuesLayoutConstraint = function (
    e
  ) {
    return e && "AfterIfContinue" == e.flagmentLayoutConstraintType;
  }),
  ((e) => {
    (e.isInstanceOfRepetitiveElementsOwnerFormattingContext = function (e) {
      return (
        !!e &&
        ("RepetitiveElementsOwner" === e.formattingContextType ||
          _t.isInstanceOfTableFormattingContext(e))
      );
    }),
      (e.isInstanceOfRepetitiveElementsOwnerLayoutConstraint = function (e) {
        return (
          !!e &&
          ("RepetitiveElementsOwner" === e.flagmentLayoutConstraintType ||
            _t.isInstanceOfTableRowLayoutConstraint(e))
        );
      });
  })(on || (on = {})),
  ((e) => {
    (e.isInstanceOfTableFormattingContext = function (e) {
      return e && "Table" === e.formattingContextType;
    }),
      (e.isInstanceOfTableRowLayoutConstraint = function (e) {
        return e && "TableRow" === e.flagmentLayoutConstraintType;
      });
  })(_t || (_t = {})),
  ((e) => {
    let t;
    var i;
    let n;
    var r;
    ((i = t = e.Whitespace || (e.Whitespace = {}))[(i.IGNORE = 0)] = "IGNORE"),
      (i[(i.NEWLINE = 1)] = "NEWLINE"),
      (i[(i.PRESERVE = 2)] = "PRESERVE"),
      ((r = n = e.ShadowType || (e.ShadowType = {}))[(r.NONE = 0)] = "NONE"),
      (r[(r.CONTENT = 1)] = "CONTENT"),
      (r[(r.ROOTLESS = 2)] = "ROOTLESS"),
      (r[(r.ROOTED = 3)] = "ROOTED");
  })(Rt || (Rt = {}));
var Wh = { transform: !0, "transform-origin": !0 },
  $h = { top: !0, bottom: !0, left: !0, right: !0 },
  Hn = class {
    constructor(e, t, i) {
      (this.target = e), (this.name = t), (this.value = i);
    }
  },
  dC = {
    show: function (e) {
      e.style.visibility = "visible";
    },
    hide: function (e) {
      e.style.visibility = "hidden";
    },
    play: function (e) {
      (e.currentTime = 0), e.play();
    },
    pause: function (e) {
      e.pause();
    },
    resume: function (e) {
      e.play();
    },
    mute: function (e) {
      e.muted = !0;
    },
    unmute: function (e) {
      e.muted = !1;
    },
  };
function pC(e, t) {
  let i = dC[t];
  return i
    ? () => {
        for (let t = 0; t < e.length; t++)
          try {
            i(e[t]);
          } catch (e) {}
      }
    : null;
}
var Un = class e extends kn {
  constructor(e, t) {
    super(),
      (this.container = e),
      (this.bleedBox = t),
      p(this, "pageAreaElement", null),
      p(this, "delayedItems", []),
      p(this, "hrefHandler"),
      p(this, "elementsById", {}),
      p(this, "dimensions", { width: 0, height: 0 }),
      p(this, "isFirstPage", !1),
      p(this, "isLastPage", !1),
      p(this, "isBlankPage", !1),
      p(this, "isAutoPageWidth", !0),
      p(this, "isAutoPageHeight", !0),
      p(this, "spineIndex", 0),
      p(this, "position", null),
      p(this, "offset", -1),
      p(this, "side", null),
      p(this, "fetchers", []),
      p(this, "marginBoxes", { top: {}, bottom: {}, left: {}, right: {} }),
      p(this, "pageType", null),
      (this.hrefHandler = (e) => {
        let t = e.currentTarget,
          i =
            t.getAttribute("href") ||
            t.getAttributeNS("http://www.w3.org/1999/xlink", "href");
        if (i) {
          let n = {
            type: "hyperlink",
            target: null,
            currentTarget: null,
            anchorElement: t,
            href: i,
            preventDefault() {
              e.preventDefault();
            },
          };
          this.dispatchEvent(n);
        }
      });
  }
  setAutoPageWidth(t) {
    (this.isAutoPageWidth = t),
      t
        ? this.container.setAttribute(e.AUTO_PAGE_WIDTH_ATTRIBUTE, "true")
        : this.container.removeAttribute(e.AUTO_PAGE_WIDTH_ATTRIBUTE);
  }
  setAutoPageHeight(t) {
    (this.isAutoPageHeight = t),
      t
        ? this.container.setAttribute(e.AUTO_PAGE_HEIGHT_ATTRIBUTE, "true")
        : this.container.removeAttribute(e.AUTO_PAGE_HEIGHT_ATTRIBUTE);
  }
  registerElementWithId(e, t) {
    let i = this.elementsById[t];
    i ? i.push(e) : (this.elementsById[t] = [e]);
  }
  finish(e, t) {
    Object.keys(this.elementsById).forEach((e) => {
      let t = this.elementsById[e];
      for (let e = 0; e < t.length; )
        this.container.contains(t[e]) ? e++ : t.splice(e, 1);
      0 === t.length && delete this.elementsById[e];
    });
    let i = this.delayedItems;
    for (let e = 0; e < i.length; e++) {
      let t = i[e];
      (t.target === this.container &&
        "transform" === t.name &&
        !this.isAutoPageWidth &&
        !this.isAutoPageHeight) ||
        w(t.target, t.name, t.value.toString());
    }
    let n = t.getElementClientRect(this.container);
    (this.dimensions.width = n.width), (this.dimensions.height = n.height);
    for (let t = 0; t < e.length; t++) {
      let i = e[t],
        n = this.elementsById[i.ref],
        r = this.elementsById[i.observer];
      if (n && r) {
        let e = pC(n, i.action);
        if (e)
          for (let t = 0; t < r.length; t++)
            r[t].addEventListener(i.event, e, !1);
      }
    }
  }
};
p(Un, "AUTO_PAGE_WIDTH_ATTRIBUTE", "data-vivliostyle-auto-page-width"),
  p(Un, "AUTO_PAGE_HEIGHT_ATTRIBUTE", "data-vivliostyle-auto-page-height");
var go = Un,
  Ht = "data-adapt-spec",
  Ze = Rt.Whitespace;
function ea(e) {
  switch (e) {
    case "normal":
    case "nowrap":
      return Ze.IGNORE;
    case "pre-line":
      return Ze.NEWLINE;
    case "pre":
    case "pre-wrap":
    case "break-spaces":
      return Ze.PRESERVE;
    default:
      return null;
  }
}
function de(e, t) {
  if (!e) return !0;
  if (1 == e.nodeType) return !1;
  let i = e.textContent;
  switch (t) {
    case Ze.PRESERVE:
      return 0 == i.length;
    case Ze.NEWLINE:
      return !!i.match(/^[ \t]*$/);
    case Ze.IGNORE:
    default:
      return !!i.match(/^[ \t\r\n\f]*$/);
  }
}
var Yr = class {
    constructor(e, t) {
      (this.flowName = e),
        (this.parentFlowName = t),
        p(this, "forcedBreakOffsets", []),
        p(this, "formattingContext", null);
    }
  },
  qr = class {
    constructor(e, t, i, n, r, s, o, a, l) {
      (this.flowName = e),
        (this.element = t),
        (this.startOffset = i),
        (this.priority = n),
        (this.linger = r),
        (this.exclusive = s),
        (this.repeated = o),
        (this.last = a),
        (this.breakBefore = l),
        p(this, "startPage", -1);
    }
    isBetter(e) {
      return (
        !!this.exclusive &&
        (!e.exclusive || this.priority > e.priority || this.last)
      );
    }
  };
function Xh(e, t) {
  return e.top - t.top;
}
function jh(e, t) {
  return t.right - e.right;
}
function Yh(e, t) {
  var i, n;
  return (
    e === t ||
    (!(!e || !t) &&
      (e.node === t.node ||
        (!!e.shadowContext &&
          !!t.shadowContext &&
          e.shadowType === Rt.ShadowType.ROOTLESS &&
          t.shadowType === Rt.ShadowType.ROOTLESS &&
          (null == (i = e.node) ? void 0 : i.outerHTML) ===
            (null == (n = t.node) ? void 0 : n.outerHTML))) &&
      e.shadowType === t.shadowType &&
      Mc(e.shadowContext, t.shadowContext) &&
      Mc(e.nodeShadow, t.nodeShadow) &&
      Yh(e.shadowSibling, t.shadowSibling))
  );
}
function Ut(e, t) {
  if (e === t) return !0;
  if (
    !e ||
    !t ||
    e.offsetInNode !== t.offsetInNode ||
    e.after !== t.after ||
    e.steps.length !== t.steps.length
  )
    return !1;
  for (let i = 0; i < e.steps.length; i++)
    if (!Yh(e.steps[i], t.steps[i])) return !1;
  return !0;
}
function qh(e) {
  return {
    steps: [
      {
        node: e,
        shadowType: Et.NONE,
        shadowContext: null,
        nodeShadow: null,
        shadowSibling: null,
        formattingContext: null,
        fragmentIndex: 0,
      },
    ],
    offsetInNode: 0,
    after: !1,
    preprocessedTextContent: null,
  };
}
function vs(e, t) {
  return {
    steps: [
      {
        node: e.sourceNode,
        shadowType: Et.NONE,
        shadowContext: e.shadowContext,
        nodeShadow: null,
        shadowSibling: null,
        formattingContext: null,
        fragmentIndex: null != t ? t : e.fragmentIndex,
      },
    ],
    offsetInNode: 0,
    after: !1,
    preprocessedTextContent: e.preprocessedTextContent,
  };
}
function Co(e, t) {
  let i = new zn(e.node, t, 0);
  return (
    (i.shadowType = e.shadowType),
    (i.shadowContext = e.shadowContext),
    (i.nodeShadow = e.nodeShadow),
    (i.shadowSibling = e.shadowSibling ? Co(e.shadowSibling, t.copy()) : null),
    (i.formattingContext = e.formattingContext),
    (i.fragmentIndex = e.fragmentIndex + 1),
    i
  );
}
var Et = Rt.ShadowType,
  Ns = class {
    constructor(e, t, i, n, r, s, o) {
      (this.owner = e),
        (this.root = t),
        (this.xmldoc = i),
        (this.parentShadow = n),
        (this.type = s),
        (this.styler = o),
        p(this, "subShadow", null),
        r && (r.subShadow = this);
    }
    equals(e) {
      return (
        !!e &&
        this.owner === e.owner &&
        this.xmldoc === e.xmldoc &&
        this.type === e.type &&
        Mc(this.parentShadow, e.parentShadow)
      );
    }
  };
function Mc(e, t) {
  return e === t || (!!e && !!t && e.equals(t));
}
var Kr = class {
    constructor(e, t) {
      (this.outer = e), (this.count = t);
    }
  },
  zn = class e {
    constructor(e, t, i) {
      (this.sourceNode = e),
        (this.parent = t),
        (this.boxOffset = i),
        p(this, "offsetInNode", 0),
        p(this, "after", !1),
        p(this, "shadowType"),
        p(this, "shadowContext"),
        p(this, "nodeShadow", null),
        p(this, "shadowSibling", null),
        p(this, "shared", !1),
        p(this, "inline", !0),
        p(this, "overflow", !1),
        p(this, "breakPenalty"),
        p(this, "display", null),
        p(this, "floatReference"),
        p(this, "floatSide", null),
        p(this, "clearSide", null),
        p(this, "floatMinWrapBlock", null),
        p(this, "columnSpan", null),
        p(this, "verticalAlign", "baseline"),
        p(this, "captionSide", "top"),
        p(this, "inlineBorderSpacing", 0),
        p(this, "blockBorderSpacing", 0),
        p(this, "flexContainer", !1),
        p(this, "whitespace"),
        p(this, "hyphenateCharacter"),
        p(this, "breakWord"),
        p(this, "establishesBFC", !1),
        p(this, "containingBlockForAbsolute", !1),
        p(this, "breakBefore", null),
        p(this, "breakAfter", null),
        p(this, "viewNode", null),
        p(this, "clearSpacer", null),
        p(this, "inheritedProps"),
        p(this, "vertical"),
        p(this, "direction"),
        p(this, "firstPseudo"),
        p(this, "lang", null),
        p(this, "preprocessedTextContent", null),
        p(this, "formattingContext"),
        p(this, "repeatOnBreak", null),
        p(this, "pluginProps", {}),
        p(this, "fragmentIndex", 1),
        p(this, "afterIfContinues", null),
        p(this, "footnotePolicy", null),
        p(this, "pageType"),
        (this.shadowType = Et.NONE),
        (this.shadowContext = t ? t.shadowContext : null),
        (this.breakPenalty = t ? t.breakPenalty : 0),
        (this.floatReference = _n.FloatReference.INLINE),
        (this.whitespace = t ? t.whitespace : Ze.IGNORE),
        (this.hyphenateCharacter = t ? t.hyphenateCharacter : null),
        (this.breakWord = !!t && t.breakWord),
        (this.inheritedProps = t ? t.inheritedProps : {}),
        (this.vertical = !!t && t.vertical),
        (this.direction = t ? t.direction : "ltr"),
        (this.firstPseudo = t ? t.firstPseudo : null),
        (this.formattingContext = t ? t.formattingContext : null),
        (this.pageType = t ? t.pageType : null);
    }
    resetView() {
      (this.inline = !0),
        (this.breakPenalty = this.parent ? this.parent.breakPenalty : 0),
        (this.viewNode = null),
        (this.clearSpacer = null),
        (this.offsetInNode = 0),
        (this.after = !1),
        (this.display = null),
        (this.floatReference = _n.FloatReference.INLINE),
        (this.floatSide = null),
        (this.clearSide = null),
        (this.floatMinWrapBlock = null),
        (this.columnSpan = null),
        (this.verticalAlign = "baseline"),
        (this.flexContainer = !1),
        (this.whitespace = this.parent ? this.parent.whitespace : Ze.IGNORE),
        (this.hyphenateCharacter = this.parent
          ? this.parent.hyphenateCharacter
          : null),
        (this.breakWord = !!this.parent && this.parent.breakWord),
        (this.breakBefore = null),
        (this.breakAfter = null),
        (this.nodeShadow = null),
        (this.establishesBFC = !1),
        (this.containingBlockForAbsolute = !1),
        (this.vertical = !!this.parent && this.parent.vertical),
        (this.nodeShadow = null),
        (this.preprocessedTextContent = null),
        (this.formattingContext = this.parent
          ? this.parent.formattingContext
          : null),
        (this.repeatOnBreak = null),
        (this.pluginProps = {}),
        (this.fragmentIndex = 1),
        (this.afterIfContinues = null),
        (this.footnotePolicy = null);
    }
    cloneItem() {
      let t = new e(this.sourceNode, this.parent, this.boxOffset);
      return (
        (t.offsetInNode = this.offsetInNode),
        (t.after = this.after),
        (t.nodeShadow = this.nodeShadow),
        (t.shadowType = this.shadowType),
        (t.shadowContext = this.shadowContext),
        (t.shadowSibling = this.shadowSibling),
        (t.inline = this.inline),
        (t.breakPenalty = this.breakPenalty),
        (t.display = this.display),
        (t.floatReference = this.floatReference),
        (t.floatSide = this.floatSide),
        (t.clearSide = this.clearSide),
        (t.floatMinWrapBlock = this.floatMinWrapBlock),
        (t.columnSpan = this.columnSpan),
        (t.verticalAlign = this.verticalAlign),
        (t.captionSide = this.captionSide),
        (t.inlineBorderSpacing = this.inlineBorderSpacing),
        (t.blockBorderSpacing = this.blockBorderSpacing),
        (t.establishesBFC = this.establishesBFC),
        (t.containingBlockForAbsolute = this.containingBlockForAbsolute),
        (t.flexContainer = this.flexContainer),
        (t.whitespace = this.whitespace),
        (t.hyphenateCharacter = this.hyphenateCharacter),
        (t.breakWord = this.breakWord),
        (t.breakBefore = this.breakBefore),
        (t.breakAfter = this.breakAfter),
        (t.viewNode = this.viewNode),
        (t.clearSpacer = this.clearSpacer),
        (t.firstPseudo = this.firstPseudo),
        (t.vertical = this.vertical),
        (t.overflow = this.overflow),
        (t.preprocessedTextContent = this.preprocessedTextContent),
        (t.formattingContext = this.formattingContext),
        (t.repeatOnBreak = this.repeatOnBreak),
        (t.pluginProps = Object.create(this.pluginProps)),
        (t.fragmentIndex = this.fragmentIndex),
        (t.afterIfContinues = this.afterIfContinues),
        (t.footnotePolicy = this.footnotePolicy),
        t
      );
    }
    modify() {
      return this.shared ? this.cloneItem() : this;
    }
    copy() {
      let e = this;
      do {
        if (e.shared) break;
        (e.shared = !0), (e = e.parent);
      } while (e);
      return this;
    }
    clone() {
      let e,
        t = this.cloneItem(),
        i = t;
      for (; null != (e = i.parent); )
        (e = e.cloneItem()), (i.parent = e), (i = e);
      return t;
    }
    toNodePositionStep() {
      var e;
      return {
        node: this.sourceNode,
        shadowType: this.shadowType,
        shadowContext: this.shadowContext,
        nodeShadow: this.nodeShadow,
        shadowSibling: this.shadowSibling
          ? this.shadowSibling.toNodePositionStep()
          : null,
        formattingContext: this.formattingContext,
        fragmentIndex:
          null === (null == (e = this.viewNode) ? void 0 : e.parentNode)
            ? 0
            : this.fragmentIndex,
      };
    }
    toNodePosition() {
      var e, t, i;
      let n = this,
        r = [];
      n.shadowType === Rt.ShadowType.ROOTLESS &&
        (n.floatReference !== _n.FloatReference.INLINE ||
          "footnote" === n.floatSide) &&
        null !=
          (i =
            null == (t = null == (e = n.shadowContext) ? void 0 : e.styler)
              ? void 0
              : t.style) &&
        i._pseudos &&
        (n = n.parent);
      do {
        (!n.firstPseudo ||
          !n.parent ||
          n.parent.firstPseudo === n.firstPseudo) &&
          r.push(n.toNodePositionStep()),
          (n = n.parent);
      } while (n);
      return {
        steps: r,
        offsetInNode: this.preprocessedTextContent
          ? zh(this.preprocessedTextContent, this.offsetInNode)
          : this.offsetInNode,
        after: this.after,
        preprocessedTextContent: this.preprocessedTextContent,
      };
    }
    isInsideBFC() {
      let e = this.parent;
      for (; e; ) {
        if (e.establishesBFC) return !0;
        e = e.parent;
      }
      return !1;
    }
    getContainingBlockForAbsolute() {
      let e = this.parent;
      for (; e; ) {
        if (e.containingBlockForAbsolute) return e;
        e = e.parent;
      }
      return null;
    }
    belongsTo(e) {
      return (
        this.formattingContext === e &&
        !!this.parent &&
        this.parent.formattingContext === e
      );
    }
  },
  ht = class e {
    constructor(e) {
      (this.primary = e), p(this, "floats", null);
    }
    clone() {
      let t = new e(this.primary);
      if (this.floats) {
        t.floats = [];
        for (let e = 0; e < this.floats.length; ++e)
          t.floats[e] = this.floats[e];
      }
      return t;
    }
    isSamePosition(e) {
      if (!e) return !1;
      if (this === e) return !0;
      if (!Ut(this.primary, e.primary)) return !1;
      if (this.floats) {
        if (!e.floats || this.floats.length !== e.floats.length) return !1;
        for (let t = 0; t < this.floats.length; t++)
          if (!Ut(this.floats[t], e.floats[t])) return !1;
      } else if (e.floats) return !1;
      return !0;
    }
  },
  Zr = class e {
    constructor(e, t) {
      (this.chunkPosition = e), (this.flowChunk = t);
    }
    clone() {
      return new e(this.chunkPosition.clone(), this.flowChunk);
    }
    isSamePosition(e) {
      return (
        !!e &&
        (this === e || this.chunkPosition.isSamePosition(e.chunkPosition))
      );
    }
  },
  Qr = class e {
    constructor() {
      p(this, "positions", []),
        p(this, "startBreakType", null),
        p(this, "breakAfter", null);
    }
    clone() {
      let t = new e(),
        i = this.positions,
        n = t.positions;
      for (let e = 0; e < i.length; e++) n[e] = i[e].clone();
      return (
        (t.startBreakType = this.startBreakType),
        (t.breakAfter = this.breakAfter),
        t
      );
    }
    isSamePosition(e) {
      if (this === e) return !0;
      if (!e || this.positions.length !== e.positions.length) return !1;
      for (let t = 0; t < this.positions.length; t++)
        if (!this.positions[t].isSamePosition(e.positions[t])) return !1;
      return !0;
    }
    hasContent(e) {
      return (
        this.positions.length > 0 &&
        this.positions[0].flowChunk.startOffset <= e
      );
    }
  },
  Jr = class e {
    constructor() {
      p(this, "page", 0),
        p(this, "flows", {}),
        p(this, "flowPositions", {}),
        p(this, "isBlankPage", !1),
        p(this, "highestSeenOffset", 0),
        p(this, "highestSeenNode"),
        p(this, "lookupPositionOffset");
    }
    clone() {
      let t = new e();
      (t.page = this.page),
        (t.isBlankPage = this.isBlankPage),
        (t.highestSeenNode = this.highestSeenNode),
        (t.highestSeenOffset = this.highestSeenOffset),
        (t.lookupPositionOffset = this.lookupPositionOffset),
        (t.flows = this.flows);
      for (let e in this.flowPositions)
        t.flowPositions[e] = this.flowPositions[e].clone();
      return t;
    }
    isSamePosition(e) {
      if (this === e) return !0;
      if (!e || this.page !== e.page) return !1;
      let t = Object.keys(this.flowPositions),
        i = Object.keys(e.flowPositions);
      if (t.length !== i.length) return !1;
      for (let i of t)
        if (!this.flowPositions[i].isSamePosition(e.flowPositions[i]))
          return !1;
      return !0;
    }
    hasContent(e, t) {
      let i = this.flowPositions[e];
      return !!i && i.hasContent(t);
    }
    startSideOfFlow(e) {
      var t;
      let i = null == (t = this.flowPositions[e]) ? void 0 : t.startBreakType;
      return i && Mn(i) ? i : "any";
    }
    firstFlowChunkOfFlow(e) {
      let t = this.flowPositions[e];
      if (!t) return null;
      let i = t.positions[0];
      return i ? i.flowChunk : null;
    }
  },
  mo = class {
    constructor(e) {
      (this.element = e),
        p(this, "left", 0),
        p(this, "top", 0),
        p(this, "marginLeft", 0),
        p(this, "marginRight", 0),
        p(this, "marginTop", 0),
        p(this, "marginBottom", 0),
        p(this, "borderLeft", 0),
        p(this, "borderRight", 0),
        p(this, "borderTop", 0),
        p(this, "borderBottom", 0),
        p(this, "paddingLeft", 0),
        p(this, "paddingRight", 0),
        p(this, "paddingTop", 0),
        p(this, "paddingBottom", 0),
        p(this, "width", 0),
        p(this, "height", 0),
        p(this, "originX", 0),
        p(this, "originY", 0),
        p(this, "exclusions", null),
        p(this, "innerShape", null),
        p(this, "computedBlockSize", 0),
        p(this, "snapWidth", 0),
        p(this, "snapHeight", 0),
        p(this, "snapOffsetX", 0),
        p(this, "snapOffsetY", 0),
        p(this, "vertical", !1),
        p(this, "rtl", !1),
        p(this, "borderBoxSizing", !1);
    }
    getInsetTop() {
      return (
        this.marginTop +
        (this.borderBoxSizing ? 0 : this.borderTop + this.paddingTop)
      );
    }
    getInsetBottom() {
      return (
        this.marginBottom +
        (this.borderBoxSizing ? 0 : this.borderBottom + this.paddingBottom)
      );
    }
    getInsetLeft() {
      return (
        this.marginLeft +
        (this.borderBoxSizing ? 0 : this.borderLeft + this.paddingLeft)
      );
    }
    getInsetRight() {
      return (
        this.marginRight +
        (this.borderBoxSizing ? 0 : this.borderRight + this.paddingRight)
      );
    }
    getInsetBefore() {
      return this.vertical ? this.getInsetRight() : this.getInsetTop();
    }
    getInsetAfter() {
      return this.vertical ? this.getInsetLeft() : this.getInsetBottom();
    }
    getInsetStart() {
      return this.vertical ? this.getInsetTop() : this.getInsetLeft();
    }
    getInsetEnd() {
      return this.vertical ? this.getInsetBottom() : this.getInsetRight();
    }
    getBeforeEdge(e) {
      return this.vertical ? e.right : e.top;
    }
    getAfterEdge(e) {
      return this.vertical ? e.left : e.bottom;
    }
    getStartEdge(e) {
      return this.vertical
        ? this.rtl
          ? e.bottom
          : e.top
        : this.rtl
        ? e.right
        : e.left;
    }
    getEndEdge(e) {
      return this.vertical
        ? this.rtl
          ? e.top
          : e.bottom
        : this.rtl
        ? e.left
        : e.right;
    }
    getInlineSize(e) {
      return this.vertical ? e.bottom - e.top : e.right - e.left;
    }
    getBoxSize(e) {
      return this.vertical ? e.right - e.left : e.bottom - e.top;
    }
    getBoxDir() {
      return this.vertical ? -1 : 1;
    }
    getInlineDir() {
      return this.rtl ? -1 : 1;
    }
    copyFrom(e) {
      (this.element = e.element),
        (this.left = e.left),
        (this.top = e.top),
        (this.marginLeft = e.marginLeft),
        (this.marginRight = e.marginRight),
        (this.marginTop = e.marginTop),
        (this.marginBottom = e.marginBottom),
        (this.borderLeft = e.borderLeft),
        (this.borderRight = e.borderRight),
        (this.borderTop = e.borderTop),
        (this.borderBottom = e.borderBottom),
        (this.paddingLeft = e.paddingLeft),
        (this.paddingRight = e.paddingRight),
        (this.paddingTop = e.paddingTop),
        (this.paddingBottom = e.paddingBottom),
        (this.width = e.width),
        (this.height = e.height),
        (this.originX = e.originX),
        (this.originY = e.originY),
        (this.innerShape = e.innerShape),
        (this.exclusions = e.exclusions),
        (this.computedBlockSize = e.computedBlockSize),
        (this.snapWidth = e.snapWidth),
        (this.snapHeight = e.snapHeight),
        (this.vertical = e.vertical),
        (this.rtl = e.rtl),
        (this.borderBoxSizing = e.borderBoxSizing);
    }
    setVerticalPosition(e, t) {
      (this.top = e),
        (this.height = t),
        w(this.element, "top", `${e}px`),
        w(this.element, "height", `${t}px`);
    }
    setHorizontalPosition(e, t) {
      (this.left = e),
        (this.width = t),
        w(this.element, "left", `${e}px`),
        w(this.element, "width", `${t}px`);
    }
    setBlockPosition(e, t) {
      if (this.vertical) {
        let i =
          t +
          this.marginLeft +
          this.marginRight +
          (this.borderBoxSizing
            ? 0
            : this.paddingLeft +
              this.paddingRight +
              this.borderLeft +
              this.borderRight);
        this.setHorizontalPosition(e + i * this.getBoxDir(), t);
      } else this.setVerticalPosition(e, t);
    }
    setInlinePosition(e, t) {
      if (this.vertical)
        if (this.rtl) {
          let i =
            t +
            this.marginTop +
            this.marginBottom +
            (this.borderBoxSizing
              ? 0
              : this.paddingTop +
                this.paddingBottom +
                this.borderTop +
                this.borderBottom);
          this.setVerticalPosition(e + i * this.getInlineDir(), t);
        } else this.setVerticalPosition(e, t);
      else if (this.rtl) {
        let i =
          t +
          this.marginLeft +
          this.marginRight +
          (this.borderBoxSizing
            ? 0
            : this.paddingLeft +
              this.paddingRight +
              this.borderLeft +
              this.borderRight);
        this.setHorizontalPosition(e + i * this.getInlineDir(), t);
      } else this.setHorizontalPosition(e, t);
    }
    clear() {
      let e,
        t = this.element;
      for (; (e = t.lastChild); ) t.removeChild(e);
    }
    getInnerShape() {
      let e = this.getInnerRect();
      return this.innerShape
        ? this.innerShape.withOffset(e.x1, e.y1)
        : Ko(e.x1, e.y1, e.x2, e.y2);
    }
    getInnerRect() {
      let e = this.originX + this.left + this.getInsetLeft(),
        t = this.originY + this.top + this.getInsetTop();
      return new He(e, t, e + this.width, t + this.height);
    }
    getPaddingRect() {
      let e = this.originX + this.left + this.marginLeft + this.borderLeft,
        t = this.originY + this.top + this.marginTop + this.borderTop,
        i = this.paddingLeft + this.width + this.paddingRight,
        n = this.paddingTop + this.height + this.paddingBottom;
      return new He(e, t, e + i, t + n);
    }
    getOuterShape(e, t) {
      let i = this.getOuterRect();
      return Vr(e, i.x1, i.y1, i.x2 - i.x1, i.y2 - i.y1, t);
    }
    getOuterRect() {
      let e = this.originX + this.left,
        t = this.originY + this.top,
        i = this.getInsetLeft() + this.width + this.getInsetRight(),
        n = this.getInsetTop() + this.height + this.getInsetBottom();
      return new He(e, t, e + i, t + n);
    }
  },
  mn = class extends ct {
    constructor(e, t, i, n) {
      super(),
        (this.elem = e),
        (this.context = t),
        (this.rootContentValue = i),
        (this.exprContentListener = n);
    }
    visitStrInner(e, t) {
      var i;
      if (!t) {
        if (3 === (null == (i = this.elem.lastChild) ? void 0 : i.nodeType))
          return void (this.elem.lastChild.textContent += e);
        t = this.elem.ownerDocument.createTextNode(e);
      }
      this.elem.appendChild(t);
    }
    visitStr(e) {
      return this.visitStrInner(e.str), null;
    }
    visitURL(e) {
      if (this.rootContentValue.url) this.elem.setAttribute("src", e.url);
      else {
        let t = this.elem.ownerDocument.createElementNS(
          "http://www.w3.org/1999/xhtml",
          "img"
        );
        t.setAttribute("src", e.url), this.elem.appendChild(t);
      }
      return null;
    }
    visitSpaceList(e) {
      return this.visitValues(e.values), null;
    }
    visitExpr(e) {
      let t = e.toExpr(),
        i = t.evaluate(this.context);
      if ("string" == typeof i) {
        t instanceof ee && (i = sn(t.scope, new De(i, null), "").stringValue()),
          this.elem.ownerDocument;
        let e = this.exprContentListener(t, i, this.elem.ownerDocument);
        !e &&
          i &&
          t instanceof Z &&
          t.str.startsWith("running-element-") &&
          (i = ""),
          this.visitStrInner(i, e);
      }
      return null;
    }
  };
function St(e) {
  return null != e && e !== O && e !== b.normal && e !== b.none && !M(e);
}
var le = _n.FloatReference;
function Zh(e) {
  switch (e) {
    case "inline":
      return le.INLINE;
    case "column":
      return le.COLUMN;
    case "region":
      return le.REGION;
    case "page":
      return le.PAGE;
    default:
      throw new Error(`Unknown float-reference: ${e}`);
  }
}
function Gn(e) {
  switch (e) {
    case le.INLINE:
      return !1;
    case le.COLUMN:
    case le.REGION:
    case le.PAGE:
      return !0;
    default:
      throw new Error(`Unknown float-reference: ${e}`);
  }
}
function Hc(e, t, i, n) {
  let r = t ? "vertical-rl" : "horizontal-tb";
  if (
    (("top" === e || "bottom" === e) && (e = kc(e, r, i)),
    "block-start" === e && (e = "inline-start"),
    "block-end" === e && (e = "inline-end"),
    "inline-start" === e || "inline-end" === e)
  ) {
    let t = kh(Pc(e, r, i), r);
    "line-left" === t ? (e = "left") : "line-right" === t && (e = "right");
  } else
    "inside" === e
      ? (e = "left" === n ? "right" : "left")
      : "outside" === e && (e = "left" === n ? "left" : "right");
  return (
    "left" !== e &&
      "right" !== e &&
      (V.warn(`Invalid float value: ${e}. Fallback to left.`), (e = "left")),
    e
  );
}
var ii = class {
    constructor(e, t, i, n, r, s) {
      (this.nodePosition = e),
        (this.floatReference = t),
        (this.floatSide = i),
        (this.clearSide = n),
        (this.flowName = r),
        (this.floatMinWrapBlock = s),
        p(this, "order", null),
        p(this, "id", null);
    }
    getOrder() {
      if (null === this.order)
        throw new Error("The page float is not yet added");
      return this.order;
    }
    getId() {
      if (!this.id) throw new Error("The page float is not yet added");
      return this.id;
    }
    isAllowedOnContext(e) {
      return e.isAnchorAlreadyAppeared(this.getId());
    }
    isAllowedToPrecede(e) {
      return !1;
    }
  },
  _c = class {
    constructor() {
      p(this, "floats", []), p(this, "nextPageFloatIndex", 0);
    }
    nextOrder() {
      return this.nextPageFloatIndex++;
    }
    createPageFloatId(e) {
      return `pf${e}`;
    }
    addPageFloat(e) {
      if (this.floats.findIndex((t) => Ut(t.nodePosition, e.nodePosition)) >= 0)
        throw new Error(
          "A page float with the same source node is already registered"
        );
      {
        let t = (e.order = this.nextOrder());
        (e.id = this.createPageFloatId(t)), this.floats.push(e);
      }
    }
    findPageFloatByNodePosition(e) {
      let t = this.floats.findIndex((t) => Ut(t.nodePosition, e));
      return t >= 0 ? this.floats[t] : null;
    }
    findPageFloatById(e) {
      let t = this.floats.findIndex((t) => t.id === e);
      return t >= 0 ? this.floats[t] : null;
    }
  },
  ri = class {
    constructor(e, t, i, n, r, s) {
      (this.floatReference = e),
        (this.floatSide = t),
        (this.clearSide = i),
        (this.continuations = n),
        (this.area = r),
        (this.continues = s);
    }
    hasFloat(e) {
      return this.continuations.some((t) => t.float === e);
    }
    findNotAllowedFloat(e) {
      for (let t = this.continuations.length - 1; t >= 0; t--) {
        let i = this.continuations[t].float;
        if (!i.isAllowedOnContext(e)) return i;
      }
      return null;
    }
    getOuterShape() {
      return this.area.getOuterShape(null, null);
    }
    getOuterRect() {
      return this.area.getOuterRect();
    }
    getOrder() {
      let e = this.continuations.map((e) => e.float);
      return Math.min.apply(
        null,
        e.map((e) => e.getOrder())
      );
    }
    shouldBeStashedBefore(e) {
      return this.getOrder() < e.getOrder();
    }
    addContinuations(e) {
      e.forEach((e) => {
        this.continuations.push(e);
      });
    }
    getFlowName() {
      let e = this.continuations[0].float.flowName;
      return this.continuations.every((t) => t.float.flowName === e), e;
    }
  },
  ai = class {
    constructor(e, t) {
      (this.float = e), (this.nodePosition = t);
    }
    equals(e) {
      return (
        !!e &&
        (this === e ||
          (this.float === e.float && Ut(this.nodePosition, e.nodePosition)))
      );
    }
  },
  Cn = class {
    constructor(e, t, i, n, r, s, o) {
      (this.parent = e),
        (this.floatReference = t),
        (this.container = i),
        (this.flowName = n),
        (this.generatingNodePosition = r),
        p(this, "children", []),
        p(this, "writingMode"),
        p(this, "direction"),
        p(this, "invalidated", !1),
        p(this, "floatStore"),
        p(this, "forbiddenFloats", []),
        p(this, "floatFragments", []),
        p(this, "stashedFloatFragments", []),
        p(this, "floatAnchors", {}),
        p(this, "floatsDeferredToNext", []),
        p(this, "floatsDeferredFromPrevious"),
        p(this, "layoutConstraints", []),
        p(this, "locked", !1),
        e && e.children.push(this),
        (this.writingMode = s || (e && e.writingMode) || b.horizontal_tb),
        (this.direction = o || (e && e.direction) || b.ltr),
        (this.floatStore = e ? e.floatStore : new _c());
      let a = this.getPreviousSibling();
      this.floatsDeferredFromPrevious = a
        ? [].concat(a.floatsDeferredToNext)
        : [];
    }
    getParent(e) {
      if (!this.parent) throw new Error(`No PageFloatLayoutContext for ${e}`);
      return this.parent;
    }
    getPreviousSiblingOf(e, t, i, n) {
      let r = this.children.indexOf(e);
      r < 0 && (r = this.children.length);
      for (let e = r - 1; e >= 0; e--) {
        let r = this.children[e];
        if (
          r.floatReference === t &&
          r.flowName === i &&
          Ut(r.generatingNodePosition, n)
        )
          return r;
        if (((r = r.getPreviousSiblingOf(null, t, i, n)), r)) return r;
      }
      return null;
    }
    getPreviousSibling() {
      let e,
        t = this,
        i = this.parent;
      for (; i; ) {
        if (
          ((e = i.getPreviousSiblingOf(
            t,
            this.floatReference,
            this.flowName,
            this.generatingNodePosition
          )),
          e)
        )
          return e;
        (t = i), (i = i.parent);
      }
      return null;
    }
    getContainer(e) {
      return e && e !== this.floatReference
        ? this.getParent(e).getContainer(e)
        : this.container;
    }
    setContainer(e) {
      (this.container = e), this.reattachFloatFragments();
    }
    addPageFloat(e) {
      this.floatStore.addPageFloat(e);
    }
    getPageFloatLayoutContext(e) {
      return e === this.floatReference
        ? this
        : this.getParent(e).getPageFloatLayoutContext(e);
    }
    findPageFloatByNodePosition(e) {
      return this.floatStore.findPageFloatByNodePosition(e);
    }
    forbid(e) {
      let t = e.getId(),
        i = e.floatReference;
      i === this.floatReference
        ? this.forbiddenFloats.includes(t) ||
          (this.forbiddenFloats.push(t),
          new zt().findByFloat(e).forbid(e, this))
        : this.getParent(i).forbid(e);
    }
    isForbidden(e) {
      let t = e.getId(),
        i = e.floatReference;
      return i === this.floatReference
        ? this.forbiddenFloats.includes(t)
        : this.getParent(i).isForbidden(e);
    }
    addPageFloatFragment(e, t) {
      let i = e.floatReference;
      i !== this.floatReference
        ? this.getParent(i).addPageFloatFragment(e, t)
        : this.floatFragments.includes(e) ||
          (this.floatFragments.push(e),
          this.floatFragments.sort((e, t) => e.getOrder() - t.getOrder())),
        t || this.invalidate();
    }
    removePageFloatFragment(e, t) {
      let i = e.floatReference;
      if (i !== this.floatReference)
        this.getParent(i).removePageFloatFragment(e, t);
      else {
        let i = this.floatFragments.indexOf(e);
        if (i >= 0) {
          let e = this.floatFragments.splice(i, 1)[0],
            n = e.area && e.area.element;
          n && n.parentNode && n.parentNode.removeChild(n),
            t || this.invalidate();
        }
      }
    }
    findPageFloatFragment(e) {
      if (e.floatReference !== this.floatReference)
        return this.getParent(e.floatReference).findPageFloatFragment(e);
      let t = this.floatFragments.findIndex((t) => t.hasFloat(e));
      return t >= 0 ? this.floatFragments[t] : null;
    }
    hasFloatFragments(e) {
      return (
        !(
          !(this.floatFragments.length > 0) ||
          (e && !this.floatFragments.some(e))
        ) ||
        (!!this.parent && this.parent.hasFloatFragments(e))
      );
    }
    hasContinuingFloatFragmentsInFlow(e) {
      return this.hasFloatFragments(
        (t) => t.continues && t.getFlowName() === e
      );
    }
    registerPageFloatAnchor(e, t) {
      this.floatAnchors[e.getId()] = t;
    }
    collectPageFloatAnchors() {
      let e = Object.assign({}, this.floatAnchors);
      return this.children.reduce(
        (e, t) => Object.assign(e, t.collectPageFloatAnchors()),
        e
      );
    }
    isAnchorAlreadyAppeared(e) {
      if (
        this.getDeferredPageFloatContinuations().some(
          (t) => t.float.getId() === e
        )
      )
        return !0;
      let t = this.collectPageFloatAnchors()[e];
      return (
        !!(t && this.container && this.container.element) &&
        this.container.element.contains(t)
      );
    }
    deferPageFloat(e) {
      let t = e.float;
      if (t.floatReference === this.floatReference) {
        let i = this.floatsDeferredToNext.findIndex((e) => e.float === t);
        i >= 0
          ? this.floatsDeferredToNext.splice(i, 1, e)
          : this.floatsDeferredToNext.push(e);
      } else this.getParent(t.floatReference).deferPageFloat(e);
    }
    hasPrecedingFloatsDeferredToNext(e, t) {
      if (!t && e.floatReference !== this.floatReference)
        return this.getParent(
          e.floatReference
        ).hasPrecedingFloatsDeferredToNext(e, !1);
      let i = e.getOrder();
      return (
        !!this.floatsDeferredToNext.some(
          (t) => t.float.getOrder() < i && !e.isAllowedToPrecede(t.float)
        ) ||
        (!!this.parent && this.parent.hasPrecedingFloatsDeferredToNext(e, !0))
      );
    }
    getLastFollowingFloatInFragments(e) {
      let t = e.getOrder(),
        i = null;
      if (
        (this.floatFragments.forEach((e) => {
          e.continuations.forEach((e) => {
            let n = e.float,
              r = n.getOrder();
            r > t && (!i || r > i.getOrder()) && (i = n);
          });
        }),
        this.parent)
      ) {
        let t = this.parent.getLastFollowingFloatInFragments(e);
        t && (!i || t.getOrder() > i.getOrder()) && (i = t);
      }
      return i;
    }
    getDeferredPageFloatContinuations(e) {
      e = e || this.flowName;
      let t = this.floatsDeferredFromPrevious.filter(
        (t) => !e || t.float.flowName === e
      );
      return (
        this.parent &&
          (t = this.parent.getDeferredPageFloatContinuations(e).concat(t)),
        t.sort((e, t) => e.float.getOrder() - t.float.getOrder())
      );
    }
    getPageFloatContinuationsDeferredToNext(e) {
      e = e || this.flowName;
      let t = this.floatsDeferredToNext.filter(
        (t) => !e || t.float.flowName === e
      );
      return this.parent
        ? this.parent.getPageFloatContinuationsDeferredToNext(e).concat(t)
        : t;
    }
    getFloatsDeferredToNextInChildContexts() {
      let e = [],
        t = [];
      for (let i = this.children.length - 1; i >= 0; i--) {
        let n = this.children[i];
        t.includes(n.flowName) ||
          (t.push(n.flowName),
          (e = e.concat(n.floatsDeferredToNext.map((e) => e.float))),
          (e = e.concat(n.getFloatsDeferredToNextInChildContexts())));
      }
      return e;
    }
    checkAndForbidNotAllowedFloat() {
      if (this.checkAndForbidFloatFollowingDeferredFloat()) return !0;
      for (let e = this.floatFragments.length - 1; e >= 0; e--) {
        let t = this.floatFragments[e],
          i = t.findNotAllowedFloat(this);
        if (i)
          return (
            this.locked
              ? this.invalidate()
              : (this.removePageFloatFragment(t),
                this.forbid(i),
                this.removeEndFloatFragments(t.floatSide)),
            !0
          );
      }
      return (
        !(this.floatReference !== le.REGION || !this.parent.locked) &&
        this.parent.checkAndForbidNotAllowedFloat()
      );
    }
    checkAndForbidFloatFollowingDeferredFloat() {
      let e = this.getFloatsDeferredToNextInChildContexts(),
        t = this.floatFragments.reduce(
          (e, t) => e.concat(t.continuations.map((e) => e.float)),
          []
        );
      t.sort((e, t) => t.getOrder() - e.getOrder());
      for (let i of t) {
        let t = i.getOrder();
        if (e.some((e) => !i.isAllowedToPrecede(e) && t > e.getOrder())) {
          if (this.locked) this.invalidate();
          else {
            this.forbid(i);
            let e = this.findPageFloatFragment(i);
            this.removePageFloatFragment(e);
          }
          return !0;
        }
      }
      return !1;
    }
    finish() {
      if (!this.checkAndForbidNotAllowedFloat()) {
        for (let e = this.floatsDeferredToNext.length - 1; e >= 0; e--)
          if (!this.floatsDeferredToNext[e].float.isAllowedOnContext(this)) {
            if (this.locked) return void this.invalidate();
            this.floatsDeferredToNext.splice(e, 1);
          }
        this.floatsDeferredFromPrevious.forEach((e) => {
          this.floatsDeferredToNext.findIndex((t) => e.equals(t)) >= 0 ||
            this.floatFragments.some((t) => t.hasFloat(e.float)) ||
            this.floatsDeferredToNext.push(e);
        });
      }
    }
    hasSameContainerAs(e) {
      return (
        !!this.container &&
        !!e.container &&
        this.container.element === e.container.element
      );
    }
    invalidate() {
      (this.invalidated = !0),
        !this.locked &&
          (this.container &&
            (this.children.forEach((e) => {
              this.hasSameContainerAs(e) &&
                e.floatFragments.forEach((e) => {
                  let t = e.area.element;
                  t && t.parentNode && t.parentNode.removeChild(t);
                });
            }),
            this.container.clear()),
          this.children.forEach((e) => {
            e.layoutConstraints.splice(0);
          }),
          this.children.splice(0),
          Object.keys(this.floatAnchors).forEach((e) => {
            delete this.floatAnchors[e];
          }));
    }
    detachChildren() {
      let e = this.children.splice(0);
      return (
        e.forEach((e) => {
          e.floatFragments.forEach((e) => {
            let t = e.area.element;
            t && t.parentNode && t.parentNode.removeChild(t);
          });
        }),
        e
      );
    }
    attachChildren(e) {
      e.forEach((e) => {
        this.children.push(e), e.reattachFloatFragments();
      });
    }
    isInvalidated() {
      return this.invalidated || (!!this.parent && this.parent.isInvalidated());
    }
    validate() {
      this.invalidated = !1;
    }
    toLogical(e) {
      let t = this.writingMode.toString(),
        i = this.direction.toString();
      if ("inside" === e || "outside" === e) {
        let t = !!this.container.element.closest(
          "[data-vivliostyle-page-side='left']"
        );
        e = "inside" === e ? (t ? "right" : "left") : t ? "left" : "right";
      }
      return kc(e, t, i);
    }
    toPhysical(e) {
      return Pc(e, this.writingMode.toString(), this.direction.toString());
    }
    toLogicalFloatSides(e) {
      let t = e.split(" "),
        i = [];
      for (let e of t) {
        let t = this.toLogical(e);
        i.includes(t) || i.push(t);
      }
      let n = [],
        r = !1,
        s = !1;
      for (let e = 0; e < i.length; e++) {
        let t = i[e];
        t.includes("block")
          ? r ||
            (i.slice(e + 1).some((e) => e.includes("block"))
              ? (n.push("snap-block"), (r = !0))
              : n.push(t))
          : t.includes("inline") &&
            (s ||
              (i.slice(e + 1).some((e) => e.includes("inline"))
                ? (n.push("snap-inline"), (s = !0))
                : n.push(t)));
      }
      return n;
    }
    removeEndFloatFragments(e) {
      let t = this.toLogicalFloatSides(e)[0];
      if ("block-end" === t || "inline-end" === t) {
        let e = 0;
        for (; e < this.floatFragments.length; ) {
          let i = this.floatFragments[e];
          this.toLogicalFloatSides(i.floatSide)[0] === t
            ? this.removePageFloatFragment(i)
            : e++;
        }
      }
    }
    stashEndFloatFragments(e) {
      let t = e.floatReference;
      if (t !== this.floatReference)
        return void this.getParent(t).stashEndFloatFragments(e);
      let i = this.toLogicalFloatSides(e.floatSide)[0];
      if (
        "block-end" === i ||
        "snap-block" === i ||
        "inline-end" === i ||
        "snap-inline" === i
      ) {
        let t = 0;
        for (; t < this.floatFragments.length; ) {
          let n = this.floatFragments[t],
            r = this.toLogicalFloatSides(n.floatSide)[0];
          (r === i ||
            ("snap-block" === i && "block-end" === r) ||
            ("snap-inline" === i && "inline-end" === r)) &&
          n.shouldBeStashedBefore(e)
            ? (this.stashedFloatFragments.push(n),
              this.floatFragments.splice(t, 1))
            : t++;
        }
      }
    }
    restoreStashedFragments(e) {
      e === this.floatReference
        ? (this.stashedFloatFragments.forEach((e) => {
            this.addPageFloatFragment(e, !0);
          }),
          this.stashedFloatFragments.splice(0))
        : this.getParent(e).restoreStashedFragments(e);
    }
    discardStashedFragments(e) {
      e === this.floatReference
        ? this.stashedFloatFragments.splice(0)
        : this.getParent(e).discardStashedFragments(e);
    }
    getStashedFloatFragments(e) {
      return e === this.floatReference
        ? this.stashedFloatFragments
            .concat()
            .sort((e, t) => t.getOrder() - e.getOrder())
        : this.getParent(e).getStashedFloatFragments(e);
    }
    getLimitValue(e, t, i, n, r) {
      this.container;
      let s = this.toLogical(e),
        o = this.toPhysical(e),
        a = t && this.toLogical(t),
        l = t && this.toPhysical(t),
        h = this.getLimitValueInner(s, a, i, n, r);
      if (this.parent && this.parent.container) {
        let e = this.parent.getLimitValue(o, l, i, n, r);
        switch (o) {
          case "top":
          case "left":
            return Math.max(h, e);
          case "bottom":
          case "right":
            return Math.min(h, e);
        }
      }
      return h;
    }
    getLimitValueInner(e, t, i, n, r) {
      this.container;
      let s = this.getLimitValuesInner(i, n, r, e, t);
      switch (e) {
        case "block-start":
          return this.container.vertical ? s.right : s.top;
        case "block-end":
          return this.container.vertical ? s.left : s.bottom;
        case "inline-start":
          return this.container.vertical
            ? this.container.rtl
              ? s.bottom
              : s.top
            : this.container.rtl
            ? s.right
            : s.left;
        case "inline-end":
          return this.container.vertical
            ? this.container.rtl
              ? s.top
              : s.bottom
            : this.container.rtl
            ? s.left
            : s.right;
        default:
          throw new Error(`Unknown logical side: ${e}`);
      }
    }
    getLimitValuesInner(e, t, i, n, r) {
      this.container;
      let s = this.container.originX,
        o = this.container.originY,
        a = this.container.getPaddingRect(),
        l = {
          top: a.y1 - o,
          left: a.x1 - s,
          bottom: a.y2 - o,
          right: a.x2 - s,
          floatMinWrapBlockStart: 0,
          floatMinWrapBlockEnd: 0,
        };
      function h(i, n, r) {
        return "%" === i.unit
          ? (r * i.num) / 100
          : e.convertLengthToPx(i, n, t);
      }
      let u = this.floatFragments;
      return (
        u.length > 0 &&
          (l = u.reduce((e, t) => {
            if (i && !i(t, this)) return e;
            let [s, o] = this.toLogicalFloatSides(t.floatSide);
            if (
              o &&
              ((n && o.includes("block") === n.includes("block") && o !== n) ||
                (r && o.includes("block") === r.includes("block") && o !== r))
            )
              return e;
            let l = t.area,
              u = t.continuations[0].float.floatMinWrapBlock,
              c = e.top,
              d = e.left,
              p = e.bottom,
              f = e.right,
              g = e.floatMinWrapBlockStart,
              m = e.floatMinWrapBlockEnd;
            switch (s) {
              case "inline-start":
                l.vertical
                  ? (c = Math.max(c, l.top + l.height))
                  : (d = Math.max(d, l.left + l.width));
                break;
              case "block-start":
                l.vertical
                  ? (u &&
                      l.left < f &&
                      (g = h(u, l.rootViewNodes[0], a.x2 - a.x1)),
                    (f = Math.min(f, l.left)))
                  : (u &&
                      l.top + l.height > c &&
                      (g = h(u, l.rootViewNodes[0], a.y2 - a.y1)),
                    (c = Math.max(c, l.top + l.height)));
                break;
              case "inline-end":
                l.vertical
                  ? (p = Math.min(p, l.top))
                  : (f = Math.min(f, l.left));
                break;
              case "block-end":
                l.vertical
                  ? (u &&
                      l.left + l.width > d &&
                      (m = h(u, l.rootViewNodes[0], a.x2 - a.x1)),
                    (d = Math.max(d, l.left + l.width)))
                  : (u &&
                      l.top < p &&
                      (m = h(u, l.rootViewNodes[0], a.y2 - a.y1)),
                    (p = Math.min(p, l.top)));
                break;
              default:
                throw new Error(`Unknown logical float side: ${s}`);
            }
            return {
              top: c,
              left: d,
              bottom: p,
              right: f,
              floatMinWrapBlockStart: g,
              floatMinWrapBlockEnd: m,
            };
          }, l)),
        (l.left += s),
        (l.right += s),
        (l.top += o),
        (l.bottom += o),
        l
      );
    }
    setFloatAreaDimensions(e, t, i, n, r, s, o) {
      if (t !== this.floatReference)
        return this.getParent(t).setFloatAreaDimensions(e, t, i, n, r, s, o);
      let a = this.toLogicalFloatSides(i);
      if ("snap-block" === a[0]) {
        if (!o["block-start"] && !o["block-end"]) return null;
      } else if (a[0].includes("block") && !o[a[0]]) return null;
      e.clientLayout;
      let l,
        h,
        u,
        c,
        d = a.find(
          (e) =>
            e.includes("inline") &&
            o["inline-start" === e ? "inline-end" : "inline-start"]
        ),
        p = a.find((e) => e.includes("block")),
        f = this.getLimitValue(
          "block-start",
          d,
          e.layoutContext,
          e.clientLayout
        ),
        g = this.getLimitValue("block-end", d, e.layoutContext, e.clientLayout),
        m = this.getLimitValue(
          "inline-start",
          p,
          e.layoutContext,
          e.clientLayout
        ),
        w = this.getLimitValue(
          "inline-end",
          p,
          e.layoutContext,
          e.clientLayout
        ),
        b = e.vertical ? e.originX : e.originY,
        v = e.vertical ? e.originY : e.originX;
      function y(t, i) {
        let n = t(e.bands, i);
        return n
          ? (e.vertical && (n = Lr(n)),
            (f = e.vertical ? Math.min(f, n.x2) : Math.max(f, n.y1)),
            (g = e.vertical ? Math.max(g, n.x1) : Math.min(g, n.y2)),
            !0)
          : s;
      }
      if (
        ((f = e.vertical
          ? Math.min(
              f,
              e.left + e.getInsetLeft() + e.width + e.getInsetRight() + b
            )
          : Math.max(f, e.top + b)),
        (g = e.vertical
          ? Math.max(g, e.left + b)
          : Math.min(
              g,
              e.top + e.getInsetTop() + e.height + e.getInsetBottom() + b
            )),
        r)
      ) {
        let t = e.vertical ? oo(new He(g, m, f, w)) : new He(m, f, w, g);
        if (
          (("block-start" === a[0] ||
            "snap-block" === a[0] ||
            "inline-start" === a[0] ||
            "snap-inline" === a[0]) &&
            !y(ah, t)) ||
          (("block-end" === a[0] ||
            "snap-block" === a[0] ||
            "inline-end" === a[0] ||
            "snap-inline" === a[0]) &&
            !y(lh, t)) ||
          ((u = (g - f) * e.getBoxDir()),
          (l = u - e.getInsetBefore() - e.getInsetAfter()),
          (c = (w - m) * e.getInlineDir()),
          (h = c - e.getInsetStart() - e.getInsetEnd()),
          !s && (l <= 0 || h <= 0))
        )
          return null;
      } else {
        (l = e.computedBlockSize),
          (u = l + e.getInsetBefore() + e.getInsetAfter());
        let t = (g - f) * e.getBoxDir();
        if ("snap-block" === a[0]) {
          if (null === n) a[0] = "block-start";
          else {
            let e = this.container.getPaddingRect(),
              t =
                this.container.getBoxDir() *
                (n - (this.container.vertical ? e.x2 : e.y1)),
              i =
                this.container.getBoxDir() *
                ((this.container.vertical ? e.x1 : e.y2) - n - u);
            a[0] = t <= i ? "block-start" : "block-end";
          }
          if (!o[a[0]]) {
            if (!o["block-end"]) return null;
            a[0] = "block-end";
          }
        } else if ("snap-inline" === a[0])
          if (null === n) a[0] = "inline-start";
          else if (o["inline-start"]) a[0] = "inline-start";
          else {
            if (!o["inline-end"]) return null;
            a[0] = "inline-end";
          }
        if (!s && t < u) return null;
        (h =
          "inline-start" === a[0] || "inline-end" === a[0] || a[1]
            ? po(e.clientLayout, e.element, ["fit-content inline size"])[
                "fit-content inline size"
              ]
            : e.adjustContentRelativeSize
            ? e.getContentInlineSize()
            : e.vertical
            ? e.height
            : e.width),
          (c = h + e.getInsetStart() + e.getInsetEnd());
        let i = (w - m) * e.getInlineDir();
        if (!s && i < c) return null;
      }
      return (
        (f -= b),
        (g -= b),
        (m -= v),
        (w -= v),
        a.some((e) => "inline-start" === e || "snap-inline" === e) ||
        (1 === a.length && ("block-start" === a[0] || "snap-block" === a[0]))
          ? e.setInlinePosition(m, h)
          : (a.some((e) => "inline-end" === e) ||
              (1 === a.length && "block-end" === a[0])) &&
            e.setInlinePosition(w - c * e.getInlineDir(), h),
        a.some((e) => "block-start" === e || "snap-block" === e) ||
        (1 === a.length && ("inline-start" === a[0] || "snap-inline" === a[0]))
          ? e.setBlockPosition(f, l)
          : (a.some((e) => "block-end" === e) ||
              (1 === a.length && "inline-end" === a[0])) &&
            e.setBlockPosition(g - u * e.getBoxDir(), l),
        a.join(" ")
      );
    }
    getFloatFragmentExclusions() {
      let e = this.floatFragments.map((e) => e.getOuterShape());
      return this.parent
        ? this.parent.getFloatFragmentExclusions().concat(e)
        : e;
    }
    reattachFloatFragments() {
      let e = this.container.element && this.container.element.parentNode;
      e &&
        this.floatFragments.forEach((t) => {
          e.appendChild(t.area.element);
        });
    }
    getMaxReachedAfterEdge() {
      let e = this.getContainer().vertical;
      return this.floatFragments.reduce(
        (t, i) => {
          let n = i.getOuterRect();
          return e ? Math.min(t, n.x1) : Math.max(t, n.y2);
        },
        e ? 1 / 0 : 0
      );
    }
    getBlockStartEdgeOfBlockEndFloats() {
      let e = this.getContainer().vertical;
      return this.floatFragments
        .filter((e) => "block-end" === e.floatSide)
        .reduce(
          (t, i) => {
            let n = i.getOuterRect();
            return e ? Math.max(t, n.x2) : Math.min(t, n.y1);
          },
          e ? 0 : 1 / 0
        );
    }
    getPageFloatClearEdge(e, t) {
      function i(e) {
        return (t) => e.isAnchorAlreadyAppeared(t.float.getId());
      }
      function n(t, n) {
        return (
          ("inline-start" !== e ||
            (!t.floatSide.includes("inline-end") &&
              "block-end" !== t.floatSide)) &&
          ("inline-end" !== e ||
            (!t.floatSide.includes("inline-start") &&
              "block-start" !== t.floatSide)) &&
          t.continuations.some(i(n))
        );
      }
      let r = t.getPaddingRect(),
        s = t.vertical ? r.x1 : r.y2,
        o = this;
      for (; o; ) {
        if (o.floatsDeferredToNext.some(i(o))) return s;
        o = o.parent;
      }
      function a(e) {
        return (
          e.floatFragments.some((t) => n(t, e)) || e.children.some((e) => a(e))
        );
      }
      if ("column" === e || "region" === e || "page" === e)
        for (o = this; o; o = o.parent)
          if (o.floatReference === e) {
            if (a(o)) return s;
            break;
          }
      t.clientLayout;
      let l = this.getLimitValue(
        "block-start",
        null,
        t.layoutContext,
        t.clientLayout,
        n
      );
      return this.getLimitValue(
        "block-end",
        null,
        t.layoutContext,
        t.clientLayout,
        n
      ) *
        t.getBoxDir() <
        s * t.getBoxDir()
        ? s
        : l;
    }
    getPageFloatPlacementCondition(e, t, i) {
      if (e.floatReference !== this.floatReference)
        return this.getParent(e.floatReference).getPageFloatPlacementCondition(
          e,
          t,
          i
        );
      let n = {
        "block-start": !0,
        "block-end": !0,
        "inline-start": !0,
        "inline-end": !0,
      };
      if (!i) return n;
      let r,
        s = this.toLogicalFloatSides(t)[0],
        o = this.toLogical(i);
      r =
        "all" === o
          ? ["block-start", "block-end", "inline-start", "inline-end"]
          : "both" === o
          ? ["inline-start", "inline-end"]
          : "same" === o
          ? "snap-block" === s
            ? ["block-start", "block-end"]
            : [s]
          : [o];
      let a = e.getOrder();
      function l(e) {
        return (t) => (!e || t.floatSide.includes(e)) && t.getOrder() < a;
      }
      function h(e, t) {
        return e.children.some((e) => e.floatFragments.some(l(t)) || h(e, t));
      }
      function u(e, t) {
        let i = e.parent;
        return !!i && (i.floatFragments.some(l(t)) || u(i, t));
      }
      if ("column" === i || "region" === i || "page" === i) {
        for (let e = this; e; e = e.parent)
          if (e.floatReference === i) {
            (e.floatFragments.some(l(null)) || h(e, null)) &&
              ((n["block-start"] = !1),
              (n["block-end"] = !1),
              (n["inline-start"] = !1),
              (n["inline-end"] = !1));
            break;
          }
        return n;
      }
      return (
        r.forEach((e) => {
          switch (e) {
            case "block-start":
              n[e] = !h(this, e);
              break;
            case "block-end":
              n[e] = !u(this, e);
              break;
            case "inline-start":
            case "inline-end":
              n[e] = !this.floatFragments.some(l(e));
              break;
            default:
              throw new Error(`Unexpected side: ${e}`);
          }
        }),
        n
      );
    }
    getLayoutConstraints() {
      return (this.parent ? this.parent.getLayoutConstraints() : []).concat(
        this.layoutConstraints
      );
    }
    addLayoutConstraint(e, t) {
      t === this.floatReference
        ? this.layoutConstraints.push(e)
        : this.getParent(t).addLayoutConstraint(e, t);
    }
    isColumnFullWithPageFloats(e) {
      let t = e.layoutContext,
        i = e.clientLayout,
        n = this,
        r = null;
      for (; n && n.container; ) {
        let s = n.getLimitValuesInner(t, i);
        r
          ? e.vertical
            ? (s.right < r.right &&
                ((r.right = s.right),
                (r.floatMinWrapBlockStart = s.floatMinWrapBlockStart)),
              s.left > r.left &&
                ((r.left = s.left),
                (r.floatMinWrapBlockEnd = s.floatMinWrapBlockEnd)))
            : (s.top > r.top &&
                ((r.top = s.top),
                (r.floatMinWrapBlockStart = s.floatMinWrapBlockStart)),
              s.bottom < r.bottom &&
                ((r.bottom = s.bottom),
                (r.floatMinWrapBlockEnd = s.floatMinWrapBlockEnd)))
          : (r = s),
          (n = n.parent);
      }
      let s = Math.max(r.floatMinWrapBlockStart, r.floatMinWrapBlockEnd);
      return (e.vertical ? r.right - r.left : r.bottom - r.top) <= s;
    }
    getMaxBlockSizeOfPageFloats() {
      let e = this.getContainer().vertical;
      return this.floatFragments.length
        ? Math.max.apply(
            null,
            this.floatFragments.map((t) => {
              let i = t.area;
              return e ? i.width : i.height;
            })
          )
        : 0;
    }
    lock() {
      this.locked = !0;
    }
    unlock() {
      this.locked = !1;
    }
    isLocked() {
      return this.locked;
    }
  },
  oi = [],
  zt = class {
    static register(e) {
      oi.push(e);
    }
    findByNodeContext(e) {
      for (let t = oi.length - 1; t >= 0; t--) {
        let i = oi[t];
        if (i.appliesToNodeContext(e)) return i;
      }
      throw new Error(`No PageFloatLayoutStrategy found for ${e}`);
    }
    findByFloat(e) {
      for (let t = oi.length - 1; t >= 0; t--) {
        let i = oi[t];
        if (i.appliesToFloat(e)) return i;
      }
      throw new Error(`No PageFloatLayoutStrategy found for ${e}`);
    }
  },
  Uc = class {
    appliesToNodeContext(e) {
      return Gn(e.floatReference);
    }
    appliesToFloat(e) {
      return !0;
    }
    createPageFloat(e, t, i) {
      let n = e.floatReference;
      e.floatSide;
      let r = e.floatSide,
        s = e.toNodePosition();
      return i
        .resolveFloatReferenceFromColumnSpan(n, e.columnSpan, e)
        .thenAsync((i) => {
          (n = i), t.flowName;
          let o = new ii(s, n, r, e.clearSide, t.flowName, e.floatMinWrapBlock);
          return t.addPageFloat(o), T(o);
        });
    }
    createPageFloatFragment(e, t, i, n, r) {
      let s = e[0].float;
      return new ri(s.floatReference, t, i, e, n, r);
    }
    findPageFloatFragment(e, t) {
      return t.findPageFloatFragment(e);
    }
    adjustPageFloatArea(e, t, i) {}
    forbid(e, t) {}
  };
zt.register(new Uc());
var fC = ri,
  li = class e extends ii {
    constructor(e, t, i, n, r) {
      super(e, t, "block-end", null, i, r), (this.footnotePolicy = n);
    }
    isAllowedToPrecede(t) {
      return !(t instanceof e);
    }
  },
  na = class extends fC {
    constructor(e, t, i, n) {
      super(e, "block-end", null, t, i, n);
    }
    getOrder() {
      return 1 / 0;
    }
    shouldBeStashedBefore(e) {
      return e instanceof li || this.getOrder() < e.getOrder();
    }
  },
  zc = class {
    constructor(e) {
      this.footnote = e;
    }
    allowLayout(e) {
      return !Ut(e.toNodePosition(), this.footnote.nodePosition);
    }
  },
  Gc = class {
    appliesToNodeContext(e) {
      return "footnote" === e.floatSide;
    }
    appliesToFloat(e) {
      return e instanceof li;
    }
    createPageFloat(e, t, i) {
      let n = le.REGION,
        r = t.getPageFloatLayoutContext(n);
      t.getPageFloatLayoutContext(le.PAGE).hasSameContainerAs(r) &&
        (n = le.PAGE);
      let s = e.toNodePosition();
      t.flowName;
      let o = new li(s, n, t.flowName, e.footnotePolicy, e.floatMinWrapBlock);
      return t.addPageFloat(o), T(o);
    }
    createPageFloatFragment(e, t, i, n, r) {
      let s = e[0].float;
      return new na(s.floatReference, e, n, r);
    }
    findPageFloatFragment(e, t) {
      let i = t
        .getPageFloatLayoutContext(e.floatReference)
        .floatFragments.filter((e) => e instanceof na);
      return i.length, i[0] || null;
    }
    adjustPageFloatArea(e, t, i) {
      (e.isFootnote = !0), (e.adjustContentRelativeSize = !1);
      let n = e.element;
      (e.vertical = i.layoutContext.applyFootnoteStyle(
        t.vertical,
        i.layoutContext.nodeContext &&
          "rtl" === i.layoutContext.nodeContext.direction,
        n
      )),
        e.convertPercentageSizesToPx(n),
        i.setComputedInsets(n, e),
        i.setComputedWidthAndHeight(n, e);
    }
    forbid(e, t) {
      let i = e;
      switch (i.footnotePolicy) {
        case b.line: {
          let e = new zc(i);
          t.addLayoutConstraint(e, i.floatReference);
          break;
        }
      }
    }
  };
zt.register(new Gc());
var gC = "data-vivliostyle-flow-root";
function Jh(e) {
  return "true" === e.getAttribute(gC);
}
function Qh(e) {
  let t,
    i = (null == e ? void 0 : e.toString()) || "block";
  switch (i) {
    case "inline-flex":
      t = "flex";
      break;
    case "inline-grid":
      t = "grid";
      break;
    case "inline-table":
      t = "table";
      break;
    case "inline":
    case "table-row-group":
    case "table-column":
    case "table-column-group":
    case "table-header-group":
    case "table-footer-group":
    case "table-row":
    case "table-cell":
    case "table-caption":
    case "inline-block":
      t = "block";
      break;
    default:
      t = i;
  }
  return L(t);
}
function sa(e) {
  return e === b.absolute || e === b.fixed;
}
function ci(e) {
  return e instanceof At && "running" === e.name;
}
function Wc(e, t, i, n) {
  return (
    e === b.none ||
      (sa(t)
        ? ((i = b.none), (e = Qh(e)))
        : ((i && i !== b.none && !M(i)) || n) && (e = Qh(e))),
    { display: e, position: t, float: i }
  );
}
function oa(e, t, i, n) {
  return Wc(e, t, i, n).display === b.block;
}
function Gt(e) {
  switch (e.toString()) {
    case "inline":
    case "inline-block":
    case "inline-flex":
    case "inline-grid":
    case "ruby":
    case "inline-table":
      return !0;
    default:
      return !1;
  }
}
function ef(e) {
  switch (e.toString()) {
    case "block":
    case "flex":
    case "grid":
    case "table":
    case "list-item":
    case "flow-root":
      return !0;
    default:
      return !1;
  }
}
function ia(e) {
  switch (e.toString()) {
    case "ruby-base":
    case "ruby-text":
    case "ruby-base-container":
    case "ruby-text-container":
      return !0;
    default:
      return !1;
  }
}
function tf(e, t, i, n, r, s, o) {
  return (
    (r = r || s || b.horizontal_tb),
    !!o ||
      (!!i && i !== b.none && !M(i)) ||
      sa(t) ||
      e === b.inline_block ||
      e === b.table_cell ||
      e === b.table_caption ||
      e == b.flex ||
      e == b.grid ||
      e == b.flow_root ||
      ((e === b.block || e === b.list_item) &&
        !!n &&
        n !== b.visible &&
        n !== b.clip &&
        !M(n)) ||
      (!!s && r !== s)
  );
}
function nf(e) {
  return e === b.relative || e === b.absolute || e === b.fixed;
}
var Nt = 1e5;
function di(e) {
  let t = e.element;
  (t.style.columnCount = "1"),
    (t.style.columnGap = Nt - (e.vertical ? e.height : e.width) + "px"),
    (t.style.columnFill = "auto");
}
function Xc(e) {
  let t = e.element;
  (t.style.columnCount = ""),
    (t.style.columnGap = ""),
    (t.style.columnFill = "");
}
function jc(e) {
  return "1" === e.element.style.columnCount;
}
function bo(e, t) {
  let i = t ? e.bottom : Math.max(Math.abs(e.left), Math.abs(e.right));
  return Math.round(i / Nt);
}
function $c(e, t) {
  let i = bo(e, t);
  if (0 === i) return 0;
  let n = t ? e.top : Math.min(Math.abs(e.left), Math.abs(e.right)),
    r = Math.round(n / Nt),
    s = r * Nt,
    o = i * Nt;
  return (
    t
      ? ((e.top -= s),
        (e.bottom -= o),
        e.bottom < e.top && ((e.bottom += Nt), (o -= Nt)),
        (e.right -= s),
        (e.left -= o))
      : e.left < -Nt / 2
      ? ((e.right += s),
        (e.left += o),
        e.left > e.right && ((e.left -= Nt), (o += Nt)),
        (e.top += s),
        (e.bottom += o))
      : ((e.left -= s),
        (e.right -= o),
        e.right < e.left && ((e.right += Nt), (o -= Nt)),
        (e.top += s),
        (e.bottom += o)),
    (e.width = e.right - e.left),
    (e.height = e.bottom - e.top),
    r
  );
}
function Yc(e, t) {
  for (let i = 0; i < e.length; i++) $c(e[i], t);
}
function vt(e, t, i) {
  var n;
  let r = e.getElementClientRect(t),
    s = $c(r, i);
  if (1 === s) {
    let o = e.getElementComputedStyle(t);
    if (
      "table-cell" === o.display ||
      ("-vivliostyle-table-cell-container" === t.className &&
        null != (n = t.parentElement) &&
        n.parentElement &&
        "table-cell" ===
          (o = e.getElementComputedStyle(t.parentElement.parentElement))
            .display)
    ) {
      let n = t.closest("[data-vivliostyle-column]"),
        a = null == n ? void 0 : n.style,
        l = i ? "width" : "height",
        h = a && parseFloat(a[l]);
      if (h) {
        let u = h,
          c = s,
          d = parseFloat(o.paddingBlockEnd),
          p = parseFloat(o.borderBlockEndWidth),
          f = Math.ceil(d + p);
        for (; f-- > 0 && c === s && --u > 0; ) {
          a[l] = `${u}px`;
          let o = e.getElementClientRect(t);
          if (
            ((c = $c(o, i)),
            c < s || (c === s && (i ? o.right > r.right : o.top < r.top)))
          )
            return (
              n.setAttribute("data-vivliostyle-column-height-adjusted", "true"),
              o
            );
        }
        a[l] = `${h}px`;
      }
    }
  }
  return r;
}
function sf(e, t) {
  var i, n;
  if (1 === e.nodeType) {
    let t = e;
    "column" === (null == (i = t.style) ? void 0 : i.breakAfter) &&
      (t.style.breakAfter = "");
  }
  if (1 === t.nodeType) {
    let e = t;
    "column" === (null == (n = e.style) ? void 0 : n.breakBefore) &&
      (e.style.breakBefore = "");
  }
}
function qc(e) {
  for (let t = 1 === e.nodeType ? e : e.parentElement; t; t = t.parentElement) {
    let e = t.style;
    if (!e || t.hasAttribute("data-vivliostyle-column")) break;
    if (!isNaN(parseFloat(e.columnCount)) || !isNaN(parseFloat(e.columnWidth)))
      return t;
    if ("absolute" === e.position) break;
  }
  return null;
}
function of(e) {
  var t;
  if (1 !== e.nodeType) return;
  let i = e,
    n = i.style,
    r = "column" === (null == n ? void 0 : n.breakBefore),
    s = i.previousElementSibling,
    o = s && "column" === (null == (t = s.style) ? void 0 : t.breakAfter);
  if (!r && !o) return;
  let a = qc(i);
  if (!a) return;
  let l = a.ownerDocument.defaultView.getComputedStyle(a),
    { writingMode: h, direction: u, columnGap: c, fontSize: d } = l,
    p = "vertical-rl" === h || "vertical-lr" === h,
    f = "rtl" === u,
    g = (parseFloat(c) || parseFloat(d) || 16) / 2,
    m = a.getBoundingClientRect(),
    w = i.getBoundingClientRect();
  (p
    ? w.top > m.bottom + g
    : f
    ? w.right < m.left - g
    : w.left > m.right + g) &&
    (r && (n.breakBefore = ""),
    o && s && (s.style.breakAfter = ""),
    (n.marginBlockStart = `${Nt}px`));
}
function Wn(e, t, i, n) {
  let r = e.viewNode;
  if (!r) return NaN;
  let s = 1 === r.nodeType ? r : r.parentElement;
  if (s && "http://www.w3.org/1999/xhtml" === s.namespaceURI) {
    let e = s.style;
    if (
      "br" === s.localName ||
      "wbr" === s.localName ||
      (e &&
        Gt(e.display) &&
        /^([\d\.]|super|(text-)?top)/.test(e.verticalAlign))
    )
      return NaN;
  }
  if (r === s) {
    if (e.after || !e.inline) {
      if (e.after && !e.inline && s.querySelector("ruby")) {
        let e = s.parentNode,
          t = s.nextSibling;
        e.removeChild(s), e.insertBefore(s, t);
      }
      let i = vt(t, s, n);
      if (0 === i.left && 0 === i.top && 0 === i.right && 0 === i.bottom)
        return NaN;
      if (i.right >= i.left && i.bottom >= i.top)
        return e.after ? (n ? i.left : i.bottom) : n ? i.right : i.top;
    }
    return NaN;
  }
  {
    let s = NaN,
      o = r.ownerDocument.createRange(),
      a = r.textContent.length;
    if (!a) return NaN;
    e.after && (i += a),
      i >= a && (i = a - 1),
      o.setStart(r, i),
      o.setEnd(r, i + 1);
    let l = t.getRangeClientRects(o);
    return (
      Yc(l, n),
      (l = l.filter((e) => e.right > e.left && e.bottom > e.top)),
      l.length
        ? ((s = n
            ? Math.min.apply(
                null,
                l.map((e) => e.left)
              )
            : Math.max.apply(
                null,
                l.map((e) => e.bottom)
              )),
          s)
        : NaN
    );
  }
}
function pi(e, t, i) {
  let n = jc(t);
  n && Xc(t);
  let r = t.clientLayout.getElementClientRect(e);
  n && di(t);
  let s = t.getComputedMargin(e);
  return i ? r.width + s.left + s.right : r.height + s.top + s.bottom;
}
function bn(e) {
  for (; e; ) {
    if (e.parentNode === e.ownerDocument) return !1;
    e = e.parentNode;
  }
  return !0;
}
function ra(e, t) {
  var i;
  if (e)
    for (let n = e.lastChild, r = n; n !== t; n = r)
      (r = n.previousSibling),
        (1 !== n.nodeType ||
          !n.hasAttribute("data-vivliostyle-float-box-moved") ||
          "inline" !== (null == (i = t.style) ? void 0 : i.display)) &&
          e.removeChild(n);
}
function aa(e) {
  return !!e.getAttribute(Ht);
}
function Ts(e) {
  var t;
  if (1 !== (null == e ? void 0 : e.nodeType)) return !1;
  let i = e;
  if (aa(i)) return !0;
  let n = null == (t = i.style) ? void 0 : t.position;
  return "absolute" === n || "fixed" === n;
}
function xn(e) {
  let t = null == e ? void 0 : e.viewNode;
  return 1 === (null == t ? void 0 : t.nodeType) && aa(t);
}
function rf(e) {
  return "inline" !== e && (Gt(e) || ia(e));
}
function Kc(e) {
  var t;
  for (let i = e.parent; i; i = i.parent)
    if (
      ("inline" !== i.display ||
        i.vertical !== (null == (t = i.parent) ? void 0 : t.vertical)) &&
      Gt(i.display)
    )
      return i;
  return null;
}
var ws = class {
  calculateOffset(e) {
    return Xn(this.getNodeContext(), e.collectElementsOffset());
  }
  breakPositionChosen(e) {}
  getNodeContext() {
    return null;
  }
};
function Xn(e, t) {
  return {
    current: t.reduce((t, i) => t + i.calculateOffset(e), 0),
    minimum: t.reduce((t, i) => t + i.calculateMinimumOffset(e), 0),
  };
}
var rn = class extends ws {
    constructor(e, t, i, n) {
      super(),
        (this.position = e),
        (this.breakOnEdge = t),
        (this.overflows = i),
        (this.computedBlockSize = n),
        p(this, "overflowIfRepetitiveElementsDropped"),
        p(this, "isEdgeUpdated", !1),
        p(this, "edge", 0),
        (this.overflowIfRepetitiveElementsDropped = i);
    }
    findAcceptableBreak(e, t) {
      return (
        this.updateOverflows(e),
        t < this.getMinBreakPenalty() ? null : e.findEdgeBreakPosition(this)
      );
    }
    getMinBreakPenalty() {
      if (!this.isEdgeUpdated)
        throw new Error("EdgeBreakPosition.prototype.updateEdge not called");
      let e =
        this.isFirstContentOfRepetitiveElementsOwner() &&
        !this.overflowIfRepetitiveElementsDropped;
      return (
        (ho(this.breakOnEdge) ? 1 : 0) +
        (this.overflows && !e ? 3 : 0) +
        (this.position.parent ? this.position.parent.breakPenalty : 0)
      );
    }
    updateEdge(e) {
      var t;
      let i = e.calculateClonedPaddingBorder(this.position);
      if (
        ((this.edge = Wn(this.position, e.clientLayout, 0, e.vertical) + i),
        this.position.after || this.position.inline)
      ) {
        if (
          this.position.after &&
          "table-cell" ===
            (null == (t = this.position.shadowContext) ? void 0 : t.root.id)
        ) {
          let t = e.element.parentElement,
            i =
              null == t
                ? void 0
                : t.closest("table, [style*='display: table;']");
          if (i) {
            let n = "collapse" === i.style.borderCollapse,
              r = 0,
              s = 0;
            for (let o = t; o; o = o.parentElement) {
              let a = e.clientLayout.getElementComputedStyle(o);
              if (
                ((o === t || (o === i && !n)) &&
                  (r += e.parseComputedLength(a.paddingBlockEnd)),
                o === i &&
                  !n &&
                  (r += e.parseComputedLength(
                    a.borderSpacing.replace(/^\S+ (\S+)$/, "$1")
                  )),
                n
                  ? (s = Math.max(
                      s,
                      e.parseComputedLength(a.borderBlockEndWidth)
                    ))
                  : (o === t || o === i) &&
                    (s += e.parseComputedLength(a.borderBlockEndWidth)),
                o === i)
              )
                break;
            }
            this.edge += (e.vertical ? -1 : 1) * (r + s);
          }
        }
      } else {
        let t = e.parseComputedLength(
          e.clientLayout.getElementComputedStyle(this.position.viewNode)
            .marginBlockStart
        );
        this.edge -= (e.vertical ? -1 : 1) * t;
      }
      this.isEdgeUpdated = !0;
    }
    updateOverflows(e) {
      this.isEdgeUpdated || this.updateEdge(e);
      let t = this.edge,
        i = this.calculateOffset(e);
      (this.overflowIfRepetitiveElementsDropped = e.isOverflown(
        t + (e.vertical ? -1 : 1) * i.minimum
      )),
        (this.overflows = this.position.overflow =
          e.isOverflown(t + (e.vertical ? -1 : 1) * i.current));
    }
    getNodeContext() {
      return this.position;
    }
    isFirstContentOfRepetitiveElementsOwner() {
      let e = this.getNodeContext();
      if (!e || !e.parent) return !1;
      let { formattingContext: t } = e.parent;
      if (!on.isInstanceOfRepetitiveElementsOwnerFormattingContext(t))
        return !1;
      let i = t.getRepetitiveElements();
      return !!i && i.isFirstContentNode(e);
    }
  },
  jn = class {
    find(e) {
      let t = Ge("RESOLVE_LAYOUT_PROCESSOR");
      for (let i = 0; i < t.length; i++) {
        let n = t[i](e);
        if (n) return n;
      }
      throw new Error(
        `No processor found for a formatting context: ${e.getName()}`
      );
    }
  },
  an = class {
    layout(e, t, i) {
      return t.isFloatNodeContext(e)
        ? t.layoutFloatOrFootnote(e)
        : t.isBreakable(e)
        ? t.layoutBreakableBlock(e)
        : t.layoutUnbreakable(e);
    }
    createEdgeBreakPosition(e, t, i, n) {
      return new rn(e.copy(), t, i, n);
    }
    startNonInlineElementNode(e) {
      return !1;
    }
    afterNonInlineElementNode(e, t) {
      return !1;
    }
    clearOverflownViewNodes(e, t, i, n) {
      var r;
      if (
        !i.viewNode ||
        !i.viewNode.parentNode ||
        (i.shadowType === Rt.ShadowType.ROOTLESS && xn(i))
      )
        return;
      let s = i.viewNode;
      "viv-ts-inner" ===
        (null == (r = s.parentElement) ? void 0 : r.localName) &&
        (s = s.parentElement.parentElement);
      let o = s.parentNode;
      ra(o, s), n && o.removeChild(s);
    }
    finishBreak(e, t, i, n) {
      let r = i || (null != t.viewNode && 1 == t.viewNode.nodeType && !t.after);
      return (
        e.clearOverflownViewNodes(t, r),
        n && e.layoutContext.processFragmentedBlockEdge(t),
        T(!0)
      );
    }
  },
  xo = class {
    constructor(e) {
      (this.parent = e), p(this, "formattingContextType", "Block");
    }
    getName() {
      return "Block formatting context (BlockFormattingContext)";
    }
    isFirstTime(e, t) {
      return t;
    }
    getParent() {
      return this.parent;
    }
    saveState() {}
    restoreState(e) {}
  },
  hi = new an(),
  af = jr.isInstanceOfBlockFormattingContext;
Ue("RESOLVE_FORMATTING_CONTEXT", (e, t, i, n, r, s) => {
  let o = e.parent;
  return (!o && e.formattingContext) ||
    (o && e.formattingContext !== o.formattingContext)
    ? null
    : e.establishesBFC || (!e.formattingContext && oa(i, n, r, s))
    ? new xo(o ? o.formattingContext : null)
    : null;
}),
  Ue("RESOLVE_LAYOUT_PROCESSOR", (e) => (e instanceof xo ? hi : null));
var Yn = class {
    constructor() {
      p(this, "initialBreakPositions", null),
        p(this, "initialStateOfFormattingContext", null),
        p(this, "initialPosition"),
        p(this, "initialFragmentLayoutConstraints");
    }
    layout(e, t) {
      return this.prepareLayout(e, t), this.tryLayout(e, t);
    }
    tryLayout(e, t) {
      let i = A("AbstractLayoutRetryer.tryLayout");
      this.saveState(e, t);
      let n = this.resolveLayoutMode(e);
      return (
        n.doLayout(e, t).then((r) => {
          let s = n.accept(r, t);
          (s = n.postLayout(r, this.initialPosition, t, s)),
            s
              ? i.finish(r)
              : (this.initialPosition,
                this.clearNodes(this.initialPosition),
                this.restoreState(e, t),
                this.tryLayout(this.initialPosition, t).thenFinish(i));
        }),
        i.result()
      );
    }
    prepareLayout(e, t) {}
    clearNodes(e) {
      let t,
        i,
        n = e.viewNode || e.parent.viewNode;
      for (; (t = n.lastChild); ) n.removeChild(t);
      for (; (i = n.nextSibling); ) i.parentNode.removeChild(i);
    }
    saveState(e, t) {
      (this.initialPosition = e.copy()),
        (this.initialBreakPositions = [].concat(t.breakPositions)),
        (this.initialFragmentLayoutConstraints = [].concat(
          t.fragmentLayoutConstraints
        )),
        e.formattingContext &&
          (this.initialStateOfFormattingContext =
            e.formattingContext.saveState());
    }
    restoreState(e, t) {
      (t.breakPositions = this.initialBreakPositions),
        (t.fragmentLayoutConstraints = this.initialFragmentLayoutConstraints),
        e.formattingContext &&
          e.formattingContext.restoreState(
            this.initialStateOfFormattingContext
          );
    }
  },
  Jc = class {
    initialState(e) {
      return { nodeContext: e, atUnforcedBreak: !1, break: !1 };
    }
    startNonDisplayableNode(e) {}
    afterNonDisplayableNode(e) {}
    startIgnoredTextNode(e) {}
    afterIgnoredTextNode(e) {}
    startNonElementNode(e) {}
    afterNonElementNode(e) {}
    startInlineElementNode(e) {}
    afterInlineElementNode(e) {}
    startNonInlineElementNode(e) {}
    afterNonInlineElementNode(e) {}
    finish(e) {}
  },
  Ps = class {
    constructor(e, t) {
      (this.strategy = e), (this.layoutContext = t);
    }
    iterate(e) {
      let t = this.strategy,
        i = t.initialState(e),
        n = A("LayoutIterator");
      return (
        n
          .loopWithFrame((e) => {
            let n;
            for (; i.nodeContext; ) {
              n = i.nodeContext.viewNode
                ? 1 !== i.nodeContext.viewNode.nodeType
                  ? de(i.nodeContext.viewNode, i.nodeContext.whitespace)
                    ? i.nodeContext.after
                      ? t.afterIgnoredTextNode(i)
                      : t.startIgnoredTextNode(i)
                    : i.nodeContext.after
                    ? t.afterNonElementNode(i)
                    : t.startNonElementNode(i)
                  : i.nodeContext.inline
                  ? i.nodeContext.after
                    ? t.afterInlineElementNode(i)
                    : t.startInlineElementNode(i)
                  : i.nodeContext.after
                  ? t.afterNonInlineElementNode(i)
                  : t.startNonInlineElementNode(i)
                : i.nodeContext.after
                ? t.afterNonDisplayableNode(i)
                : t.startNonDisplayableNode(i);
              let r = (n && n.isPending() ? n : T(!0)).thenAsync(() =>
                i.break
                  ? T(null)
                  : this.layoutContext.nextInTree(
                      i.nodeContext,
                      i.atUnforcedBreak
                    )
              );
              if (r.isPending())
                return void r.then((t) => {
                  i.break
                    ? e.breakLoop()
                    : ((i.nodeContext = t), e.continueLoop());
                });
              if (i.break) return void e.breakLoop();
              i.nodeContext = r.get();
            }
            t.finish(i), e.breakLoop();
          })
          .then(() => {
            n.finish(i.nodeContext);
          }),
        n.result()
      );
    }
  },
  yn = class extends Jc {
    constructor(e) {
      super(), (this.leadingEdge = e);
    }
    startNonInlineBox(e) {}
    endEmptyNonInlineBox(e) {}
    endNonInlineBox(e) {}
    initialState(e) {
      return {
        nodeContext: e,
        atUnforcedBreak: !!this.leadingEdge && e.after,
        break: !1,
        leadingEdge: this.leadingEdge,
        breakAtTheEdge: null,
        onStartEdges: !1,
        leadingEdgeContexts: [],
        lastAfterNodeContext: null,
      };
    }
    processForcedBreak(e, t) {
      let i = !e.leadingEdge && Ie(e.breakAtTheEdge);
      if (i) {
        let i = (e.nodeContext = e.leadingEdgeContexts[0] || e.nodeContext);
        i.viewNode.parentNode.removeChild(i.viewNode),
          (t.pageBreakType = e.breakAtTheEdge);
      }
      return i;
    }
    saveEdgeAndProcessOverflow(e, t) {
      let i = t.checkOverflowAndSaveEdgeAndBreakPosition(
        e.lastAfterNodeContext,
        null,
        !0,
        e.breakAtTheEdge
      );
      return (
        i &&
          ((e.nodeContext = (e.lastAfterNodeContext || e.nodeContext).modify()),
          (e.nodeContext.overflow = !0)),
        i
      );
    }
    processLayoutConstraint(e, t, i) {
      let n = e.nodeContext,
        r = !t.allowLayout(n);
      return (
        r &&
          (i.checkOverflowAndSaveEdgeAndBreakPosition(
            e.lastAfterNodeContext,
            null,
            !1,
            e.breakAtTheEdge
          ),
          (n = e.nodeContext = n.modify()),
          (n.overflow = !0)),
        r
      );
    }
    startNonElementNode(e) {
      e.onStartEdges = !1;
    }
    startNonInlineElementNode(e) {
      return (
        e.leadingEdgeContexts.push(e.nodeContext.copy()),
        (e.breakAtTheEdge = Ve(e.breakAtTheEdge, e.nodeContext.breakBefore)),
        (e.onStartEdges = !0),
        this.startNonInlineBox(e)
      );
    }
    afterNonInlineElementNode(e) {
      let t, i;
      return (
        e.onStartEdges
          ? ((t = this.endEmptyNonInlineBox(e)),
            (i = t && t.isPending() ? t : T(!0)),
            (i = i.thenAsync(
              () => (
                e.break ||
                  ((e.leadingEdgeContexts = []),
                  (e.leadingEdge = !1),
                  (e.atUnforcedBreak = !1),
                  (e.breakAtTheEdge = null)),
                T(!0)
              )
            )))
          : ((t = this.endNonInlineBox(e)),
            (i = t && t.isPending() ? t : T(!0))),
        i.thenAsync(
          () => (
            e.break ||
              ((e.onStartEdges = !1),
              (e.lastAfterNodeContext = e.nodeContext.copy()),
              (e.breakAtTheEdge = Ve(
                e.breakAtTheEdge,
                e.nodeContext.breakAfter
              ))),
            T(!0)
          )
        )
      );
    }
  },
  ca = [];
function cf() {
  ca = [];
}
function nu(e, t, i) {
  return (e -= i), 0 === t ? 0 === e : e % t == 0 && e / t >= 0;
}
var eu = class {
    constructor(e) {
      this.matchers = e;
    }
    matches() {
      return this.matchers.some((e) => e.matches());
    }
  },
  tu = class {
    constructor(e) {
      this.matchers = e;
    }
    matches() {
      return this.matchers.every((e) => e.matches());
    }
  },
  yo = class e {
    constructor(e, t, i) {
      (this.elementOffset = e), (this.a = t), (this.b = i);
    }
    static registerFragmentIndex(t, i, n) {
      let r = e.fragmentIndices;
      (!r[t] || r[t].priority <= n) &&
        (r[t] = { fragmentIndex: i, priority: n });
    }
    static clearFragmentIndices() {
      e.fragmentIndices = {};
    }
    matches() {
      let t = e.fragmentIndices[this.elementOffset];
      return (
        null != t &&
        null != t.fragmentIndex &&
        nu(t.fragmentIndex, this.a, this.b)
      );
    }
  };
p(yo, "fragmentIndices", {});
var qn = yo,
  ks = class {
    static buildViewConditionMatcher(e, t) {
      let i = t.split("_");
      return "NFS" == i[0]
        ? new qn(e, parseInt(i[1], 10), parseInt(i[2], 10))
        : null;
    }
    static buildAllMatcher(e) {
      return new tu(e);
    }
    static buildAnyMatcher(e) {
      return new eu(e);
    }
  },
  Gu = {
    "border-collapse": !0,
    "border-spacing": !0,
    "caption-side": !0,
    "clip-rule": !0,
    color: !0,
    "color-interpolation": !0,
    "color-rendering": !0,
    cursor: !0,
    direction: !0,
    "empty-cells": !0,
    fill: !0,
    "fill-opacity": !0,
    "fill-rule": !0,
    "font-kerning": !0,
    "font-size": !0,
    "font-size-adjust": !0,
    "font-family": !0,
    "font-feature-settings": !0,
    "font-style": !0,
    "font-stretch": !0,
    "font-variant-ligatures": !0,
    "font-variant-caps": !0,
    "font-variant-numeric": !0,
    "font-variant-east-asian": !0,
    "font-weight": !0,
    "glyph-orientation-vertical": !0,
    "hanging-punctuation": !0,
    hyphens: !0,
    "hyphenate-character": !0,
    "hyphenate-limit-chars": !0,
    "hyphenate-limit-last": !0,
    "image-rendering": !0,
    "image-resolution": !0,
    "letter-spacing": !0,
    "line-break": !0,
    "line-height": !0,
    "list-style-image": !0,
    "list-style-position": !0,
    "list-style-type": !0,
    marker: !0,
    "marker-end": !0,
    "marker-mid": !0,
    "marker-start": !0,
    orphans: !0,
    "overflow-wrap": !0,
    "paint-order": !0,
    "pointer-events": !0,
    quotes: !0,
    "ruby-align": !0,
    "ruby-position": !0,
    "shape-rendering": !0,
    stroke: !0,
    "stroke-dasharray": !0,
    "stroke-dashoffset": !0,
    "stroke-linecap": !0,
    "stroke-linejoin": !0,
    "stroke-miterlimit": !0,
    "stroke-opacity": !0,
    "stroke-width": !0,
    "tab-size": !0,
    "text-align": !0,
    "text-align-last": !0,
    "text-anchor": !0,
    "text-autospace": !0,
    "text-decoration-skip": !0,
    "text-decoration-skip-ink": !0,
    "text-emphasis-color": !0,
    "text-emphasis-position": !0,
    "text-emphasis-style": !0,
    "text-fill-color": !0,
    "text-combine-upright": !0,
    "text-indent": !0,
    "text-justify": !0,
    "text-orientation": !0,
    "text-rendering": !0,
    "text-shadow": !0,
    "text-size-adjust": !0,
    "text-spacing-trim": !0,
    "text-stroke-color": !0,
    "text-stroke-width": !0,
    "text-transform": !0,
    "text-underline-offset": !0,
    "text-underline-position": !0,
    "text-wrap": !0,
    "text-wrap-mode": !0,
    "text-wrap-style": !0,
    visibility: !0,
    "white-space": !0,
    widows: !0,
    "word-break": !0,
    "word-spacing": !0,
    "writing-mode": !0,
  },
  mC = ["image-resolution", "orphans", "widows"];
function gf() {
  return Ge("POLYFILLED_INHERITED_PROPS").reduce(
    (e, t) => e.concat(t()),
    [].concat(mC)
  );
}
var CC = {
    "http://www.idpf.org/2007/ops": !0,
    "http://www.w3.org/1999/xhtml": !0,
    "http://www.w3.org/2000/svg": !0,
    "http://www.w3.org/1998/Math/MathML": !0,
  },
  ru = [
    "margin-%",
    "padding-%",
    "border-%-width",
    "border-%-style",
    "border-%-color",
    "%",
  ],
  bC = ["max-%", "min-%", "%"],
  df = (() => {
    let e = ["left", "right", "top", "bottom"],
      t = {
        width: !0,
        height: !0,
        "max-width": !0,
        "max-height": !0,
        "min-width": !0,
        "min-height": !0,
      };
    for (let i = 0; i < ru.length; i++)
      for (let n = 0; n < e.length; n++) {
        t[ru[i].replace("%", e[n])] = !0;
      }
    return t;
  })();
function To(e, t) {
  let i = {};
  for (let t of ru)
    for (let n in e) {
      let r = t.replace("%", n),
        s = t.replace("%", e[n]);
      (i[r] = s), (i[s] = r);
    }
  for (let e of bC)
    for (let n in t) {
      let r = e.replace("%", n),
        s = e.replace("%", t[n]);
      (i[r] = s), (i[s] = r);
    }
  return i;
}
var Wu = To(
    {
      "block-start": "right",
      "block-end": "left",
      "inline-start": "top",
      "inline-end": "bottom",
    },
    { "block-size": "width", "inline-size": "height" }
  ),
  $u = To(
    {
      "block-start": "top",
      "block-end": "bottom",
      "inline-start": "left",
      "inline-end": "right",
    },
    { "block-size": "height", "inline-size": "width" }
  ),
  xC = To(
    {
      "block-start": "right",
      "block-end": "left",
      "inline-start": "bottom",
      "inline-end": "top",
    },
    { "block-size": "width", "inline-size": "height" }
  ),
  yC = To(
    {
      "block-start": "top",
      "block-end": "bottom",
      "inline-start": "right",
      "inline-end": "left",
    },
    { "block-size": "height", "inline-size": "width" }
  ),
  EC = To({ inside: "right", outside: "left" }, {}),
  SC = To({ inside: "left", outside: "right" }, {}),
  _ = class e {
    constructor(e, t) {
      (this.value = e), (this.priority = t);
    }
    getBaseValue() {
      return this;
    }
    filterValue(t) {
      let i = this.value.visit(t);
      return i === this.value ? this : new e(i, this.priority);
    }
    increaseSpecificity(t) {
      return 0 == t ? this : new e(this.value, this.priority + t);
    }
    evaluate(e, t, i, n) {
      return t && bt(t) ? this.value : Ei(e, this.value, t, i, n);
    }
    isEnabled(e) {
      return !0;
    }
  },
  gi = class e extends _ {
    constructor(e, t, i) {
      super(e, t), (this.condition = i);
    }
    getBaseValue() {
      return new _(this.value, this.priority);
    }
    filterValue(t) {
      let i = this.value.visit(t);
      return i === this.value ? this : new e(i, this.priority, this.condition);
    }
    increaseSpecificity(t) {
      return 0 == t
        ? this
        : new e(this.value, this.priority + t, this.condition);
    }
    isEnabled(e) {
      try {
        return !!this.condition.evaluate(e);
      } catch (e) {
        V.warn(e);
      }
      return !1;
    }
  };
function au(e, t, i) {
  return (!t || i.priority >= t.priority) && i.isEnabled(e)
    ? i.getBaseValue()
    : t;
}
function ln(e, t, i, n) {
  if (e)
    if (i) {
      let r = e[t];
      (!r || i.priority >= r.priority) &&
        (n ? i.isEnabled(n) && (e[t] = i.getBaseValue()) : (e[t] = i));
    } else delete e[t];
}
var mf = { "region-id": !0, "fragment-selector-id": !0 };
function NC(e) {
  return !!mf[e];
}
function lu(e) {
  return "_" === e.charAt(0) && "_viewConditionalStyles" !== e;
}
function Ls(e) {
  return "_" !== e.charAt(0) && !mf[e];
}
function Zn(e) {
  return !!Gu[e] || bt(e);
}
function he(e, t) {
  return e[t];
}
function En(e, t, i) {
  i ? (e[t] = i) : delete e[t];
}
function Tt(e, t) {
  return e[t];
}
function wo(e, t) {
  let i = e[t];
  return i || ((i = {}), (e[t] = i)), i;
}
var Cf = (e) => {
  let t = e._viewConditionalStyles;
  return t || ((t = []), (e._viewConditionalStyles = t)), t;
};
function Xu(e, t) {
  return e[t];
}
function bf(e, t) {
  let i = e[t];
  return i || ((i = []), (e[t] = i)), i;
}
function yi(e, t, i, n, r, s, o) {
  var a, l;
  if (
    ([
      { id: r, styleKey: "_pseudos" },
      { id: s, styleKey: "_regions" },
    ].forEach((e) => {
      if (e.id) {
        let i = wo(t, e.styleKey);
        (t = i[e.id]) || ((t = {}), (i[e.id] = t));
      }
    }),
    o)
  ) {
    let e = Cf(t);
    (t = {}), e.push({ styles: t, matcher: o });
  }
  for (let r in i)
    if (!lu(r))
      if (NC(r)) {
        let e = Xu(i, r),
          n = bf(t, r);
        Array.prototype.push.apply(n, e);
      } else {
        let s = he(i, r);
        if (!s.isEnabled(e)) continue;
        let o = s.increaseSpecificity(n);
        ln(t, r, o, e);
        let h =
          null ==
          (l = null == (a = e.style) ? void 0 : a.validatorSet.shorthands[r])
            ? void 0
            : l.propList;
        if (h)
          for (let i of h) {
            let n = new _(O, o.priority);
            ln(t, i, n, e);
          }
      }
}
function xf(e, t) {
  let i = {};
  for (let n = 0; n < t.length; n++) yi(e, i, t[n], 0, null, null, null);
  return i;
}
function yf(e, t) {
  if (e.length > 0) {
    e.sort((e, t) => t.getPriority() - e.getPriority());
    let i = null;
    for (let n = e.length - 1; n >= 0; n--)
      (i = e[n]), (i.chained = t), (t = i);
    return i;
  }
  return t;
}
var da = class extends Jt {
  constructor(e, t) {
    super(), (this.props = e), (this.context = t), p(this, "propName", "");
  }
  setPropName(e) {
    this.propName = e;
  }
  getFontSize() {
    let e = he(this.props, "font-size");
    if (!e.value.isNumeric()) return Y.em;
    let t = e.value;
    if (!Zp(t.unit)) throw new Error("Unexpected state");
    return t.num * Y[t.unit];
  }
  visitNumeric(e) {
    return (
      this.context,
      "font-size" === this.propName
        ? ju(e, this.getFontSize(), this.context)
        : "em" === e.unit ||
          "ex" === e.unit ||
          "rem" === e.unit ||
          "lh" === e.unit ||
          "rlh" === e.unit
        ? ba(e, this.getFontSize(), this.context)
        : e
    );
  }
  visitExpr(e) {
    return "font-size" == this.propName
      ? Ei(this.context, e, this.propName).visit(this)
      : e;
  }
};
function ba(e, t, i) {
  let n = e.unit,
    r = e.num;
  if ("em" === n || "ex" === n) {
    let e = Y[n] / Y.em;
    return new P(r * e * t, "px");
  }
  return "rem" === n
    ? new P(r * i.fontSize(), "px")
    : "rlh" === n
    ? new P(r * i.rootLineHeight, "px")
    : e;
}
function ju(e, t, i) {
  let n = (e = ba(e, t, i)).unit,
    r = e.num;
  return "px" === n
    ? e
    : new P("%" === n ? (r / 100) * t : r * i.queryUnitSize(n, !1), "px");
}
var Sn = class {
    apply(e) {}
    mergeWith(e) {
      return new cu([this, e]);
    }
    clone() {
      return this;
    }
  },
  So = class extends Sn {
    constructor(e) {
      super(), (this.conditionItem = e);
    }
    apply(e) {
      e.pushConditionItem(this.conditionItem.fresh(e));
    }
  },
  cu = class e extends Sn {
    constructor(e) {
      super(), (this.list = e);
    }
    apply(e) {
      for (let t = 0; t < this.list.length; t++) this.list[t].apply(e);
    }
    mergeWith(e) {
      return this.list.push(e), this;
    }
    clone() {
      return new e([].concat(this.list));
    }
  },
  Rs = class extends Sn {
    constructor(e, t, i, n, r) {
      super(),
        (this.style = e),
        (this.specificity = t),
        (this.pseudoelement = i),
        (this.regionId = n),
        (this.viewConditionId = r);
    }
    apply(e) {
      yi(
        e.context,
        e.currentStyle,
        this.style,
        this.specificity,
        this.pseudoelement,
        this.regionId,
        e.buildViewConditionMatcher(this.viewConditionId)
      );
    }
  },
  Q = class extends Sn {
    constructor() {
      super(), p(this, "chained", null);
    }
    apply(e) {
      this.chained.apply(e);
    }
    getPriority() {
      return 0;
    }
    makePrimary(e) {
      return !1;
    }
  },
  uu = class extends Q {
    constructor(e) {
      super(), (this.className = e);
    }
    apply(e) {
      e.currentClassNames.includes(this.className) && this.chained.apply(e);
    }
    getPriority() {
      return 10;
    }
    makePrimary(e) {
      return (
        this.chained &&
          e.insertInTable(e.classes, this.className, this.chained),
        !0
      );
    }
  },
  du = class extends Q {
    constructor(e) {
      super(), (this.id = e);
    }
    apply(e) {
      (e.currentId == this.id || e.currentXmlId == this.id) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 11;
    }
    makePrimary(e) {
      return this.chained && e.insertInTable(e.ids, this.id, this.chained), !0;
    }
  },
  pa = class extends Q {
    constructor(e) {
      super(), (this.localName = e);
    }
    apply(e) {
      e.currentLocalName == this.localName && this.chained.apply(e);
    }
    getPriority() {
      return 8;
    }
    makePrimary(e) {
      return (
        this.chained && e.insertInTable(e.tags, this.localName, this.chained),
        !0
      );
    }
  },
  pu = class extends Q {
    constructor(e, t) {
      super(), (this.ns = e), (this.localName = t);
    }
    apply(e) {
      e.currentLocalName == this.localName &&
        e.currentNamespace == this.ns &&
        this.chained.apply(e);
    }
    getPriority() {
      return 8;
    }
    makePrimary(e) {
      if (this.chained) {
        let t = e.nsPrefix[this.ns];
        t || ((t = `ns${e.nsCount++}:`), (e.nsPrefix[this.ns] = t));
        let i = t + this.localName;
        e.insertInTable(e.nstags, i, this.chained);
      }
      return !0;
    }
  },
  hu = class extends Q {
    constructor(e, t) {
      super(), (this.epubTypePatt = e), (this.targetLocalName = t);
    }
    apply(e) {
      let t = e.currentElement;
      if (
        t instanceof HTMLAnchorElement &&
        t.hash &&
        t.href == t.baseURI.replace(/#.*$/, "") + t.hash
      ) {
        let i = t.hash.substring(1),
          n = t.ownerDocument.getElementById(i);
        if (
          n &&
          (!this.targetLocalName || n.localName == this.targetLocalName)
        ) {
          let t =
            n.getAttributeNS("http://www.idpf.org/2007/ops", "type") ||
            n.getAttribute("epub:type");
          t && t.match(this.epubTypePatt) && this.chained.apply(e);
        }
      }
    }
  },
  fu = class extends Q {
    constructor(e) {
      super(), (this.ns = e);
    }
    apply(e) {
      e.currentNamespace == this.ns && this.chained.apply(e);
    }
  };
function xa(e, t, i, n) {
  if (!e) return !1;
  if (null !== t) return n(e, t, i);
  for (let t of e.getAttributeNames())
    if (
      (t === i || t.endsWith(`:${i}`)) &&
      n(e, t === i ? "" : e.lookupNamespaceURI(t.split(":")[0]), i)
    )
      return !0;
  return !1;
}
var ha = class extends Q {
    constructor(e, t) {
      super(), (this.ns = e), (this.name = t);
    }
    apply(e) {
      xa(e.currentElement, this.ns, this.name, (e, t, i) =>
        e.hasAttributeNS(t, i)
      ) && this.chained.apply(e);
    }
  },
  gu = class extends Q {
    constructor(e, t, i) {
      super(), (this.ns = e), (this.name = t), (this.value = i);
    }
    apply(e) {
      xa(
        e.currentElement,
        this.ns,
        this.name,
        (e, t, i) => e.getAttributeNS(t, i) == this.value
      ) && this.chained.apply(e);
    }
    getPriority() {
      return "type" == this.name && "http://www.idpf.org/2007/ops" == this.ns
        ? 9
        : 0;
    }
    makePrimary(e) {
      return (
        "type" == this.name &&
        "http://www.idpf.org/2007/ops" == this.ns &&
        (this.chained && e.insertInTable(e.epubtypes, this.value, this.chained),
        !0)
      );
    }
  },
  mu = class extends Q {
    constructor(e, t) {
      super(), (this.ns = e), (this.name = t);
    }
    apply(e) {
      xa(
        e.currentElement,
        this.ns,
        this.name,
        (e, t, i) => !!CC[e.getAttributeNS(t, i)]
      ) && this.chained.apply(e);
    }
    getPriority() {
      return 0;
    }
    makePrimary(e) {
      return !1;
    }
  },
  As = class extends Q {
    constructor(e, t, i) {
      super(), (this.ns = e), (this.name = t), (this.regexp = i);
    }
    apply(e) {
      xa(e.currentElement, this.ns, this.name, (e, t, i) => {
        var n;
        return !(null == (n = e.getAttributeNS(t, i)) || !n.match(this.regexp));
      }) && this.chained.apply(e);
    }
  },
  Cu = class extends Q {
    constructor(e) {
      super(), (this.langRegExp = e);
    }
    apply(e) {
      e.lang.match(this.langRegExp) && this.chained.apply(e);
    }
  },
  fa = class extends Q {
    constructor() {
      super();
    }
    apply(e) {
      e.isFirst && this.chained.apply(e);
    }
    getPriority() {
      return 6;
    }
  },
  bu = class extends Q {
    constructor() {
      super();
    }
    apply(e) {
      e.isRoot && this.chained.apply(e);
    }
    getPriority() {
      return 12;
    }
  },
  Kn = class extends Q {
    constructor(e, t) {
      super(), (this.a = e), (this.b = t);
    }
    matchANPlusB(e) {
      return nu(e, this.a, this.b);
    }
  },
  xu = class extends Kn {
    constructor(e, t) {
      super(e, t);
    }
    apply(e) {
      this.matchANPlusB(e.currentSiblingOrder) && this.chained.apply(e);
    }
    getPriority() {
      return 5;
    }
  },
  mi = class extends Kn {
    constructor(e, t) {
      super(e, t);
    }
    apply(e) {
      let t =
        e.currentSiblingTypeCounts[e.currentNamespace][e.currentLocalName];
      this.matchANPlusB(t) && this.chained.apply(e);
    }
    getPriority() {
      return 5;
    }
  },
  Ci = class extends Kn {
    constructor(e, t) {
      super(e, t);
    }
    apply(e) {
      let t = e.currentFollowingSiblingOrder;
      null === t &&
        (t = e.currentFollowingSiblingOrder =
          e.currentElement.parentNode.childElementCount -
          e.currentSiblingOrder +
          1),
        this.matchANPlusB(t) && this.chained.apply(e);
    }
    getPriority() {
      return 4;
    }
  },
  bi = class extends Kn {
    constructor(e, t) {
      super(e, t);
    }
    apply(e) {
      let t = e.currentFollowingSiblingTypeCounts;
      if (!t[e.currentNamespace]) {
        let i = e.currentElement;
        do {
          let e = i.namespaceURI,
            n = i.localName,
            r = t[e];
          r || (r = t[e] = {}), (r[n] = (r[n] || 0) + 1);
        } while ((i = i.nextElementSibling));
      }
      this.matchANPlusB(t[e.currentNamespace][e.currentLocalName]) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 4;
    }
  },
  yu = class extends Q {
    constructor() {
      super();
    }
    apply(e) {
      let t = e.currentElement.firstChild;
      for (; t; ) {
        switch (t.nodeType) {
          case Node.ELEMENT_NODE:
            return;
          case Node.TEXT_NODE:
            if (t.length > 0) return;
        }
        t = t.nextSibling;
      }
      this.chained.apply(e);
    }
    getPriority() {
      return 4;
    }
  },
  Eu = class extends Q {
    constructor() {
      super();
    }
    apply(e) {
      !1 === e.currentElement.disabled && this.chained.apply(e);
    }
    getPriority() {
      return 5;
    }
  },
  Su = class extends Q {
    constructor() {
      super();
    }
    apply(e) {
      !0 === e.currentElement.disabled && this.chained.apply(e);
    }
    getPriority() {
      return 5;
    }
  },
  Nu = class extends Q {
    constructor() {
      super();
    }
    apply(e) {
      let t = e.currentElement;
      (!0 === t.selected || !0 === t.checked) && this.chained.apply(e);
    }
    getPriority() {
      return 5;
    }
  },
  ze = class extends Q {
    constructor(e) {
      super(), (this.condition = e);
    }
    apply(e) {
      if (e.conditions[this.condition])
        try {
          e.dependentConditions.push(this.condition), this.chained.apply(e);
        } finally {
          e.dependentConditions.pop();
        }
    }
    getPriority() {
      return 5;
    }
  },
  vu = class e extends Sn {
    constructor() {
      super(), p(this, "applied", !1);
    }
    apply(e) {
      this.applied = !0;
    }
    clone() {
      let t = new e();
      return (t.applied = this.applied), t;
    }
  },
  xi = class extends Q {
    constructor(e) {
      super(),
        p(this, "checkAppliedAction"),
        p(this, "firstActions", []),
        (this.checkAppliedAction = new vu());
      for (let t of e) this.firstActions.push(yf(t, this.checkAppliedAction));
    }
    apply(e) {
      for (let t of this.firstActions)
        if ((t.apply(e), this.checkAppliedAction.applied)) break;
      this.checkAppliedAction.applied === this.positive() &&
        this.chained.apply(e),
        (this.checkAppliedAction.applied = !1);
    }
    getPriority() {
      return Math.max(
        ...this.firstActions.map((e) => (e instanceof Q ? e.getPriority() : 0))
      );
    }
    positive() {
      return !0;
    }
    relational() {
      return !1;
    }
  },
  Tu = class extends xi {
    positive() {
      return !1;
    }
  },
  wu = class extends xi {
    constructor(e) {
      super([]), (this.selectorTexts = e);
    }
    apply(e) {
      for (let t of this.selectorTexts) {
        let i, n;
        /^\s*[+~]/.test(t)
          ? ((n = e.currentElement.parentNode),
            (i = `:scope > :nth-child(${
              Array.from(n.children).indexOf(e.currentElement) + 1
            }) ${t}`))
          : ((n = e.currentElement), (i = `:scope ${t}`));
        try {
          if (n.querySelector(i)) {
            this.checkAppliedAction.apply(e);
            break;
          }
        } catch (e) {}
      }
      this.checkAppliedAction.applied && this.chained.apply(e),
        (this.checkAppliedAction.applied = !1);
    }
    relational() {
      return !0;
    }
  },
  No = class {
    constructor(e, t, i) {
      (this.condition = e),
        (this.viewConditionId = t),
        (this.viewCondition = i);
    }
    increment(e) {
      e.increment(this.condition, this.viewCondition);
    }
    decrement(e) {
      e.decrement(this.condition, this.viewCondition);
    }
    buildViewConditionMatcher(e) {
      return e.buildViewConditionMatcher(this.viewConditionId);
    }
  },
  Pu = class e extends No {
    constructor(e, t, i) {
      super(e, t, i);
    }
    fresh(t) {
      return new e(
        this.condition,
        this.viewConditionId,
        this.buildViewConditionMatcher(t)
      );
    }
    push(e, t) {
      return 0 == t && this.increment(e), !1;
    }
    pop(e, t) {
      return 0 == t && (this.decrement(e), !0);
    }
  },
  ku = class e extends No {
    constructor(e, t, i) {
      super(e, t, i);
    }
    fresh(t) {
      return new e(
        this.condition,
        this.viewConditionId,
        this.buildViewConditionMatcher(t)
      );
    }
    push(e, t) {
      return 0 == t ? this.increment(e) : 1 == t && this.decrement(e), !1;
    }
    pop(e, t) {
      return 0 == t
        ? (this.decrement(e), !0)
        : (1 == t && this.increment(e), !1);
    }
  },
  Au = class e extends No {
    constructor(e, t, i) {
      super(e, t, i), p(this, "fired", !1);
    }
    fresh(t) {
      return new e(
        this.condition,
        this.viewConditionId,
        this.buildViewConditionMatcher(t)
      );
    }
    push(e, t) {
      return !!this.fired && (this.decrement(e), !0);
    }
    pop(e, t) {
      return this.fired
        ? (this.decrement(e), !0)
        : (0 == t && ((this.fired = !0), this.increment(e)), !1);
    }
  },
  Lu = class e extends No {
    constructor(e, t, i) {
      super(e, t, i), p(this, "fired", !1);
    }
    fresh(t) {
      return new e(
        this.condition,
        this.viewConditionId,
        this.buildViewConditionMatcher(t)
      );
    }
    push(e, t) {
      return (
        this.fired &&
          (-1 == t ? this.increment(e) : 0 == t && this.decrement(e)),
        !1
      );
    }
    pop(e, t) {
      if (this.fired) {
        if (-1 == t) return this.decrement(e), !0;
        0 == t && this.increment(e);
      } else 0 == t && ((this.fired = !0), this.increment(e));
      return !1;
    }
  },
  Ru = class {
    constructor(e, t) {
      (this.afterprop = e), (this.element = t);
    }
    fresh(e) {
      return this;
    }
    push(e, t) {
      return !1;
    }
    pop(e, t) {
      return (
        0 == t &&
        (e.processPseudoelementProps(this.afterprop, this.element), !0)
      );
    }
  },
  Iu = class {
    constructor(e) {
      this.lang = e;
    }
    fresh(e) {
      return this;
    }
    push(e, t) {
      return !1;
    }
    pop(e, t) {
      return 0 == t && ((e.lang = this.lang), !0);
    }
  },
  Vu = class {
    constructor(e) {
      this.oldQuotes = e;
    }
    fresh(e) {
      return this;
    }
    push(e, t) {
      return !1;
    }
    pop(e, t) {
      return 0 == t && ((e.quotes = this.oldQuotes), !0);
    }
  },
  Fu = class extends Jt {
    constructor(e) {
      super(), (this.element = e);
    }
    createValueFromString(e, t) {
      return "url" === t ? new Oe(e || "about:invalid") : new ue(e || "");
    }
    visitFunc(e) {
      if ("attr" !== e.name) return super.visitFunc(e);
      let t = "string",
        i = null,
        n = null;
      if (e.values[0] instanceof q) {
        let n = e.values[0].values;
        n.length >= 2 && (t = n[1].stringValue()), (i = n[0].stringValue());
      } else i = e.values[0].stringValue();
      return (
        (n =
          e.values.length > 1
            ? this.createValueFromString(e.values[1].stringValue(), t)
            : this.createValueFromString(null, t)),
        this.element && this.element.hasAttribute(i)
          ? this.createValueFromString(this.element.getAttribute(i), t)
          : n
      );
    }
  };
function fi(e) {
  if (St(e)) {
    if (e instanceof ue) return e.stringValue();
    if (e instanceof q) return e.values.map((e) => fi(e)).join("");
  }
  return "";
}
var vo = class extends Jt {
  constructor(e, t, i) {
    super(), (this.cascade = e), (this.element = t), (this.counterResolver = i);
  }
  visitIdent(e) {
    let t = this.cascade,
      i = t.quotes,
      n = Math.floor(i.length / 2) - 1;
    switch (e.name) {
      case "open-quote": {
        let e = i[2 * Math.min(n, t.quoteDepth)];
        return t.quoteDepth++, e;
      }
      case "close-quote":
        return (
          t.quoteDepth > 0 && t.quoteDepth--,
          i[2 * Math.min(n, t.quoteDepth) + 1]
        );
      case "no-open-quote":
        return t.quoteDepth++, new ue("");
      case "no-close-quote":
        return t.quoteDepth > 0 && t.quoteDepth--, new ue("");
    }
    return e;
  }
  format(e, t) {
    let i,
      n = !1,
      r = !1;
    null != (i = t.match(/^upper-(.*)/))
      ? ((n = !0), (t = i[1]))
      : null != (i = t.match(/^lower-(.*)/)) && ((r = !0), (t = i[1]));
    let s = "";
    return (
      pf[t]
        ? (s = TC(pf[t], e))
        : hf[t]
        ? (s = PC(hf[t], e))
        : null != ff[t]
        ? (s = ff[t])
        : "decimal-leading-zero" == t
        ? ((s = `${e}`), 1 == s.length && (s = `0${s}`))
        : (s =
            "cjk-ideographic" == t || "trad-chinese-informal" == t
              ? AC(e, kC)
              : `${e}`),
      n ? s.toUpperCase() : r ? s.toLowerCase() : s
    );
  }
  visitFuncCounter(e) {
    let t = e[0].toString(),
      i = e.length > 1 ? e[1].stringValue() : "decimal",
      n = this.cascade.counters[t];
    if (n && n.length) {
      let e = (n && n.length && n[n.length - 1]) || 0;
      return new ue(this.format(e, i));
    }
    {
      let e = new F(
        this.counterResolver.getPageCounterVal(t, (e) => this.format(e || 0, i))
      );
      return new q([e]);
    }
  }
  visitFuncCounters(e) {
    let t = e[0].toString(),
      i = e[1].stringValue(),
      n = e.length > 2 ? e[2].stringValue() : "decimal",
      r = this.cascade.counters[t],
      s = new $e();
    if (r && r.length)
      for (let e = 0; e < r.length; e++)
        e > 0 && s.append(i), s.append(this.format(r[e], n));
    let o = new F(
      this.counterResolver.getPageCountersVal(t, (e) => {
        let t = [];
        if (e.length)
          for (let i = 0; i < e.length; i++) t.push(this.format(e[i], n));
        let r = s.toString();
        return r.length && t.push(r), t.length ? t.join(i) : this.format(0, n);
      })
    );
    return new q([o]);
  }
  visitFuncTargetCounter(e) {
    let t,
      i = e[0];
    t = i instanceof Oe ? i.url : i.stringValue();
    let n = e[1].toString(),
      r = e.length > 2 ? e[2].stringValue() : "decimal",
      s = new F(
        this.counterResolver.getTargetCounterVal(t, n, (e) =>
          this.format(e || 0, r)
        )
      );
    return new q([s]);
  }
  visitFuncTargetCounters(e) {
    let t,
      i = e[0];
    t = i instanceof Oe ? i.url : i.stringValue();
    let n = e[1].toString(),
      r = e[2].stringValue(),
      s = e.length > 3 ? e[3].stringValue() : "decimal",
      o = new F(
        this.counterResolver.getTargetCountersVal(t, n, (e) => {
          let t = e.map((e) => this.format(e, s));
          return t.length ? t.join(r) : this.format(0, s);
        })
      );
    return new q([o]);
  }
  visitFuncTargetText(e) {
    let t,
      i = e[0];
    t = i instanceof Oe ? i.url : i.stringValue();
    let n = e.length > 1 ? e[1].stringValue() : "content",
      r = new F(this.counterResolver.getTargetTextVal(t, n));
    return new q([r]);
  }
  visitFuncString(e) {
    let t = e.length > 0 ? e[0].stringValue() : "",
      i = e.length > 1 ? e[1].stringValue() : "first";
    return new F(this.counterResolver.getNamedStringVal(t, i));
  }
  visitFuncElement(e) {
    let t = e.length > 0 ? e[0].stringValue() : "",
      i = e.length > 1 ? e[1].stringValue() : "first";
    return new F(this.counterResolver.getRunningElementVal(t, i));
  }
  visitFuncContent(e) {
    var t, i, n, r, s, o;
    let a = e.length > 0 ? e[0].stringValue() : "text",
      l = "";
    switch (a) {
      case "text":
        l = this.element.textContent;
        break;
      case "before":
      case "after":
        {
          let e = Tt(this.cascade.currentStyle, "_pseudos");
          l = fi(
            null ==
              (i = null == (t = null == e ? void 0 : e[a]) ? void 0 : t.content)
              ? void 0
              : i.value
          );
        }
        break;
      case "first-letter": {
        let e = Tt(this.cascade.currentStyle, "_pseudos"),
          t = (
            fi(
              null ==
                (r =
                  null == (n = null == e ? void 0 : e.before)
                    ? void 0
                    : n.content)
                ? void 0
                : r.value
            ) ||
            this.element.textContent ||
            fi(
              null ==
                (o =
                  null == (s = null == e ? void 0 : e.after)
                    ? void 0
                    : s.content)
                ? void 0
                : o.value
            )
          ).match(ls);
        l = t ? t[0] : "";
      }
    }
    return new ue(l);
  }
  visitFuncLeader(e) {
    let t = "";
    if (e[0] instanceof be)
      switch (e[0].stringValue()) {
        case "dotted":
          t = ".";
          break;
        case "solid":
          t = "_";
          break;
        case "space":
          t = " ";
      }
    else e[0] instanceof ue && (t = e[0].stringValue());
    return 0 == t.length
      ? new ue("")
      : new F(new Z(null, () => t, "viv-leader"));
  }
  visitFunc(e) {
    switch (e.name) {
      case "counter":
        if (e.values.length <= 2) return this.visitFuncCounter(e.values);
        break;
      case "counters":
        if (e.values.length <= 3) return this.visitFuncCounters(e.values);
        break;
      case "target-counter":
        if (e.values.length <= 3) return this.visitFuncTargetCounter(e.values);
        break;
      case "target-counters":
        if (e.values.length <= 4) return this.visitFuncTargetCounters(e.values);
        break;
      case "target-text":
        if (e.values.length >= 1 && e.values.length <= 2)
          return this.visitFuncTargetText(e.values);
        break;
      case "string":
        if (e.values.length <= 2) return this.visitFuncString(e.values);
        break;
      case "element":
        if (e.values.length <= 2) return this.visitFuncElement(e.values);
        break;
      case "content":
        if (e.values.length <= 1) return this.visitFuncContent(e.values);
        break;
      case "leader":
        if (e.values.length <= 1) return this.visitFuncLeader(e.values);
    }
    return e;
  }
};
function ou(e, t, i) {
  let n;
  if (1 === e.nodeType) n = Array.from(e.getClientRects());
  else {
    let i = e.ownerDocument.createRange();
    i.selectNodeContents(e), (n = t.getRangeClientRects(i));
  }
  return n.reduce(
    (e, t) =>
      e + ("vertical-rl" === i || "vertical-lr" === i ? t.height : t.width),
    0
  );
}
var vC = (e, t, i) => {
  let n = t.filter(
    (e) =>
      e.after &&
      1 === e.viewNode.nodeType &&
      e.viewNode.getAttribute("data-viv-leader")
  );
  for (let e of n) {
    let t = function (e) {
        a.textContent =
          "rtl" === d
            ? (e.startsWith("‏") ? "" : "‏") + e + (e.endsWith("‏") ? "" : "‏")
            : e;
      },
      n = function () {
        let e = i.clientLayout.getElementClientRect(l);
        return (
          "vertical-rl" === c || "vertical-lr" === c
            ? "rtl" === d
              ? (e.top -= y)
              : (e.bottom += y)
            : "rtl" === d
            ? (e.left -= y)
            : (e.right += y),
          f.left > e.left ||
            f.right < e.right ||
            f.top > e.top ||
            f.bottom < e.bottom
        );
      },
      r = function () {
        let e,
          i,
          r = u.repeat(1e4);
        if ((t(r), n())) {
          (e = 1), (i = 1e4);
          for (let r = 0; r < 16; r++) {
            let r = "",
              s = Math.floor((e + i) / 2);
            for (let e = 0; e < s; e++) r += u;
            if ((t(r), n())) i = s;
            else {
              if (e == s) return;
              e = s;
            }
          }
          t(u);
        }
      },
      s = function (e) {
        let t = 0,
          n = l.parentElement;
        for (; n && n !== o.viewNode; )
          (t += i.getComputedInsets(n)[e]), (n = n.parentElement);
        return t;
      };
    let o = e.parent;
    for (; o && o.inline; ) o = o.parent;
    let a = e.viewNode,
      l = a.parentElement,
      h = l.getAttribute("data-adapt-pseudo"),
      u = a.getAttribute("data-viv-leader-value"),
      {
        writingMode: c,
        direction: d,
        marginInlineEnd: p,
      } = i.clientLayout.getElementComputedStyle(l);
    (a.style.marginInlineStart = "1px"),
      t(u),
      (l.style.display = "inline-block"),
      (l.style.textIndent = "0");
    let f = i.clientLayout.getElementClientRect(o.viewNode),
      g = i.clientLayout.getElementClientRect(l),
      m = i.parseComputedLength(p),
      w = [],
      b = l.parentElement;
    for (; b.parentElement && b.parentElement !== o.viewNode; )
      b = b.parentElement;
    let v = b.nextSibling;
    for (; v; ) {
      if (1 === v.nodeType) {
        let e = v,
          {
            display: t,
            float: n,
            position: r,
          } = i.clientLayout.getElementComputedStyle(e);
        if ("none" !== n || "absolute" === r || "fixed" === r) {
          v = v.nextSibling;
          continue;
        }
        if (Gt(t)) w.push(e);
        else if (ef(t)) break;
      } else if (3 === v.nodeType) {
        let e = v;
        e.length > 0 && w.push(e);
      }
      v = v.nextSibling;
    }
    let y = w.reduce((e, t) => e + ou(t, i.clientLayout, c), 0);
    if ("after" !== h) {
      let e = ou(b, i.clientLayout, c),
        t = ou(l, i.clientLayout, c);
      y += e - t;
    }
    "vertical-rl" === c || "vertical-lr" === c
      ? ("rtl" === d ? (f.top += m) : (f.bottom -= m),
        (f.top = Math.min(g.top, f.top)),
        (f.bottom = Math.max(g.bottom, f.bottom)))
      : ("rtl" === d ? (f.left += m) : (f.right -= m),
        (f.left = Math.min(g.left, f.left)),
        (f.right = Math.max(g.right, f.right))),
      r();
    let x = i.clientLayout.getElementClientRect(l);
    l.style.float = "rtl" == d ? "left" : "right";
    let S = i.clientLayout.getElementClientRect(l),
      C = 0;
    (C =
      "rtl" == d
        ? "vertical-rl" == c || "vertical-lr" == c
          ? x.top - S.top - s("top")
          : x.left - S.left - s("left")
        : "vertical-rl" == c || "vertical-lr" == c
        ? S.bottom - x.bottom - s("bottom")
        : S.right - x.right - s("right")),
      (C -= y),
      (C = Math.max(0, C - 0.1)),
      (l.style.float = ""),
      (a.style.marginInlineStart = `${C}px`);
  }
};
Ue("POST_LAYOUT_BLOCK", vC);
var pf = {
    roman: [
      4999,
      1e3,
      "M",
      900,
      "CM",
      500,
      "D",
      400,
      "CD",
      100,
      "C",
      90,
      "XC",
      50,
      "L",
      40,
      "XL",
      10,
      "X",
      9,
      "IX",
      5,
      "V",
      4,
      "IV",
      1,
      "I",
    ],
    armenian: [
      9999,
      9e3,
      "ք",
      8e3,
      "փ",
      7e3,
      "ւ",
      6e3,
      "ց",
      5e3,
      "ր",
      4e3,
      "տ",
      3e3,
      "վ",
      2e3,
      "ս",
      1e3,
      "ռ",
      900,
      "ջ",
      800,
      "պ",
      700,
      "չ",
      600,
      "ո",
      500,
      "շ",
      400,
      "ն",
      300,
      "յ",
      200,
      "մ",
      100,
      "ճ",
      90,
      "ղ",
      80,
      "ձ",
      70,
      "հ",
      60,
      "կ",
      50,
      "ծ",
      40,
      "խ",
      30,
      "լ",
      20,
      "ի",
      10,
      "ժ",
      9,
      "թ",
      8,
      "ը",
      7,
      "է",
      6,
      "զ",
      5,
      "ե",
      4,
      "դ",
      3,
      "գ",
      2,
      "բ",
      1,
      "ա",
    ],
    georgian: [
      19999,
      1e4,
      "ჵ",
      9e3,
      "ჰ",
      8e3,
      "ჯ",
      7e3,
      "ჴ",
      6e3,
      "ხ",
      5e3,
      "ჭ",
      4e3,
      "წ",
      3e3,
      "ძ",
      2e3,
      "ც",
      1e3,
      "ჩ",
      900,
      "შ",
      800,
      "ყ",
      700,
      "ღ",
      600,
      "ქ",
      500,
      "ფ",
      400,
      "ჳ",
      300,
      "ტ",
      200,
      "ს",
      100,
      "რ",
      90,
      "ჟ",
      80,
      "პ",
      70,
      "ო",
      60,
      "ჲ",
      50,
      "ნ",
      40,
      "მ",
      30,
      "ლ",
      20,
      "კ",
      10,
      "ი",
      9,
      "თ",
      8,
      "ჱ",
      7,
      "ზ",
      6,
      "ვ",
      5,
      "ე",
      4,
      "დ",
      3,
      "გ",
      2,
      "ბ",
      1,
      "ა",
    ],
    hebrew: [
      999,
      400,
      "ת",
      300,
      "ש",
      200,
      "ר",
      100,
      "ק",
      90,
      "צ",
      80,
      "פ",
      70,
      "ע",
      60,
      "ס",
      50,
      "נ",
      40,
      "מ",
      30,
      "ל",
      20,
      "כ",
      19,
      "יט",
      18,
      "יח",
      17,
      "יז",
      16,
      "טז",
      15,
      "טו",
      10,
      "י",
      9,
      "ט",
      8,
      "ח",
      7,
      "ז",
      6,
      "ו",
      5,
      "ה",
      4,
      "ד",
      3,
      "ג",
      2,
      "ב",
      1,
      "א",
    ],
  },
  hf = { latin: "a-z", alpha: "a-z", greek: "α-ρσ-ω", russian: "а-ик-щэ-я" },
  ff = { square: "■", disc: "•", circle: "◦", none: "" };
function TC(e, t) {
  if (t > e[0] || t <= 0 || t != Math.round(t)) return "";
  let i = "";
  for (let n = 1; n < e.length; n += 2) {
    let r = e[n],
      s = Math.floor(t / r);
    if (s > 20) return "";
    for (t -= s * r; s > 0; ) (i += e[n + 1]), s--;
  }
  return i;
}
function wC(e) {
  let t = [],
    i = 0;
  for (; i < e.length; )
    if ("-" == e.substr(i + 1, 1)) {
      let n = e.charCodeAt(i),
        r = e.charCodeAt(i + 2);
      i += 3;
      for (let e = n; e <= r; e++) t.push(String.fromCharCode(e));
    } else t.push(e.substr(i++, 1));
  return t;
}
function PC(e, t) {
  if (t <= 0 || t != Math.round(t)) return "";
  let i = wC(e),
    n = "";
  do {
    let e = --t % i.length;
    (n = i[e] + n), (t = (t - e) / i.length);
  } while (t > 0);
  return n;
}
var kC = {
  formal: !1,
  digits: "零一二三四五六七八九",
  markers: "十百千",
  negative: "負",
};
function AC(e, t) {
  if (e > 9999 || e < -9999) return `${e}`;
  if (0 == e) return t.digits.charAt(0);
  let i = new $e();
  if ((e < 0 && (i.append(t.negative), (e = -e)), e < 10))
    i.append(t.digits.charAt(e));
  else if (!t.formal && e <= 19)
    i.append(t.markers.charAt(0)), 0 != e && i.append(t.digits.charAt(e - 10));
  else {
    let n = Math.floor(e / 1e3);
    n && (i.append(t.digits.charAt(n)), i.append(t.markers.charAt(2)));
    let r = Math.floor(e / 100) % 10;
    r && (i.append(t.digits.charAt(r)), i.append(t.markers.charAt(1)));
    let s = Math.floor(e / 10) % 10;
    s && (i.append(t.digits.charAt(s)), i.append(t.markers.charAt(0)));
    let o = e % 10;
    o && i.append(t.digits.charAt(o));
  }
  return i.toString();
}
var Yu = 1 / 1048576;
function Eo(e, t) {
  for (let i in e) t[i] = e[i].clone();
}
var Bu = class e {
    constructor() {
      p(this, "nsCount", 0),
        p(this, "nsPrefix", {}),
        p(this, "tags", {}),
        p(this, "nstags", {}),
        p(this, "epubtypes", {}),
        p(this, "classes", {}),
        p(this, "ids", {}),
        p(this, "pagetypes", {}),
        p(this, "order", 0);
    }
    clone() {
      let t = new e();
      t.nsCount = this.nsCount;
      for (let e in this.nsPrefix) t.nsPrefix[e] = this.nsPrefix[e];
      return (
        Eo(this.tags, t.tags),
        Eo(this.nstags, t.nstags),
        Eo(this.epubtypes, t.epubtypes),
        Eo(this.classes, t.classes),
        Eo(this.ids, t.ids),
        Eo(this.pagetypes, t.pagetypes),
        (t.order = this.order),
        t
      );
    }
    insertInTable(e, t, i) {
      let n = e[t];
      n && (i = n.mergeWith(i)), (e[t] = i);
    }
    createInstance(e, t, i, n) {
      return new Ou(this, e, t, i, n);
    }
    nextOrder() {
      return (this.order += Yu);
    }
  },
  Ou = class {
    constructor(e, t, i, n, r) {
      (this.context = t),
        (this.counterListener = i),
        (this.counterResolver = n),
        p(this, "code"),
        p(this, "stack", [[], []]),
        p(this, "conditions", {}),
        p(this, "currentElement", null),
        p(this, "currentElementOffset", null),
        p(this, "currentStyle", null),
        p(this, "currentClassNames", null),
        p(this, "currentLocalName", ""),
        p(this, "currentNamespace", ""),
        p(this, "currentId", ""),
        p(this, "currentXmlId", ""),
        p(this, "currentNSTag", ""),
        p(this, "currentEpubTypes", null),
        p(this, "currentPageType", null),
        p(this, "previousPageType", null),
        p(this, "firstPageType", null),
        p(this, "isFirst", !0),
        p(this, "isRoot", !0),
        p(this, "counters", {}),
        p(this, "counterScoping", [{}]),
        p(this, "quotes"),
        p(this, "quoteDepth", 0),
        p(this, "lang", ""),
        p(this, "siblingOrderStack", [0]),
        p(this, "currentSiblingOrder", 0),
        p(this, "siblingTypeCountsStack", [{}]),
        p(this, "currentSiblingTypeCounts"),
        p(this, "currentFollowingSiblingOrder", null),
        p(this, "followingSiblingOrderStack"),
        p(this, "followingSiblingTypeCountsStack", [{}]),
        p(this, "currentFollowingSiblingTypeCounts"),
        p(this, "viewConditions", {}),
        p(this, "dependentConditions", []),
        p(this, "elementStack"),
        (this.code = e),
        (this.quotes = [new ue("“"), new ue("”"), new ue("‘"), new ue("’")]),
        (this.currentSiblingTypeCounts = this.siblingTypeCountsStack[0]),
        (this.followingSiblingOrderStack = [this.currentFollowingSiblingOrder]),
        (this.currentFollowingSiblingTypeCounts =
          this.siblingTypeCountsStack[0]);
    }
    pushConditionItem(e) {
      this.stack[this.stack.length - 1].push(e);
    }
    increment(e, t) {
      (this.conditions[e] = (this.conditions[e] || 0) + 1),
        t &&
          (this.viewConditions[e]
            ? this.viewConditions[e].push(t)
            : (this.viewConditions[e] = [t]));
    }
    decrement(e, t) {
      this.conditions[e]--,
        this.viewConditions[e] &&
          ((this.viewConditions[e] = this.viewConditions[e].filter(
            (e) => e !== t
          )),
          0 === this.viewConditions[e].length && delete this.viewConditions[e]);
    }
    buildViewConditionMatcher(e) {
      let t = null;
      e &&
        (this.currentElementOffset,
        (t = ks.buildViewConditionMatcher(this.currentElementOffset, e)));
      let i = this.dependentConditions
        .map((e) => {
          let t = this.viewConditions[e];
          return t && t.length > 0
            ? 1 === t.length
              ? t[0]
              : ks.buildAnyMatcher([].concat(t))
            : null;
        })
        .filter((e) => e);
      return i.length <= 0
        ? t
        : null === t
        ? 1 === i.length
          ? i[0]
          : ks.buildAllMatcher(i)
        : ks.buildAllMatcher([t].concat(i));
    }
    applyAction(e, t) {
      let i = e[t];
      i && i.apply(this);
    }
    pushRule(e, t, i) {
      (this.currentElement = null),
        (this.currentElementOffset = null),
        (this.currentStyle = i),
        (this.currentNamespace = ""),
        (this.currentLocalName = ""),
        (this.currentId = ""),
        (this.currentXmlId = ""),
        (this.currentClassNames = e),
        (this.currentNSTag = ""),
        (this.currentEpubTypes = iu),
        (this.currentPageType = t),
        this.applyActions();
    }
    defineCounter(e, t) {
      let i = this.counterScoping[this.counterScoping.length - 1];
      i ||
        ((i = {}), (this.counterScoping[this.counterScoping.length - 1] = i)),
        this.counters[e]
          ? (i[e] && this.counters[e].pop(), this.counters[e].push(t))
          : (this.counters[e] = [t]),
        (i[e] = !0);
    }
    pushCounters(e) {
      var t, i, n, r, s, o, a;
      let l = b.inline,
        h = e.display;
      if ((h && (l = h.evaluate(this.context)), l === b.none))
        return (
          this.currentElement.setAttribute("data-viv-display-none", "true"),
          void this.counterScoping.push(null)
        );
      if (this.currentElement.closest("[data-viv-display-none]"))
        return void this.counterScoping.push(null);
      let u = b.inline,
        c = e.float;
      c && (u = c.evaluate(this.context));
      let d = null,
        p = null,
        f = null,
        g = e["counter-reset"];
      if (g) {
        let e = g.evaluate(this.context);
        e && (d = fs(e, !0));
      }
      let m = e["counter-set"];
      if (m) {
        let e = m.evaluate(this.context);
        e && (f = fs(e, !1));
      }
      let w = e["counter-increment"];
      if (w) {
        let e = w.evaluate(this.context);
        e && (p = fs(e, !1));
      }
      if (
        (("ol" == this.currentLocalName || "ul" == this.currentLocalName) &&
          "http://www.w3.org/1999/xhtml" == this.currentNamespace &&
          (d || (d = {}),
          (d["list-item"] =
            (null != (i = null == (t = this.currentElement) ? void 0 : t.start)
              ? i
              : 1) - 1)),
        l === b.list_item &&
          (p || (p = {}),
          (p["list-item"] = null != (n = p["list-item"]) ? n : 1),
          /^\s*[-+]?\d/.test(
            null !=
              (s =
                null == (r = this.currentElement)
                  ? void 0
                  : r.getAttribute("value"))
              ? s
              : ""
          ) && (f || (f = {}), (f["list-item"] = this.currentElement.value))),
        (null == (o = this.currentElement) ? void 0 : o.parentNode.nodeType) ===
          Node.DOCUMENT_NODE &&
          (d || (d = {}), void 0 === d.footnote && (d.footnote = 0)),
        u === b.footnote && (p || (p = {}), void 0 === p.footnote))
      ) {
        let e =
          null == (a = this.currentStyle["counter-increment"])
            ? void 0
            : a.value;
        (!e ||
          !(
            e === b.footnote ||
            (e instanceof q && e.values.includes(b.footnote))
          )) &&
          (p.footnote = 1);
      }
      if (d) for (let e in d) this.defineCounter(e, d[e]);
      if (p)
        for (let e in p) {
          this.counters[e] || this.defineCounter(e, 0);
          let t = this.counters[e];
          t[t.length - 1] += p[e];
        }
      if (f)
        for (let e in f)
          if (this.counters[e]) {
            let t = this.counters[e];
            t[t.length - 1] = f[e];
          } else this.defineCounter(e, f[e]);
      if (l === b.list_item) {
        let t = this.counters["list-item"],
          i = t[t.length - 1];
        e["ua-list-item-count"] = new _(new nt(i), 0);
      }
      this.counterScoping.push(null);
    }
    popCounters() {
      let e = this.counterScoping.pop();
      if (e)
        for (let t in e) {
          let e = this.counters[t];
          e && (1 == e.length ? delete this.counters[t] : e.pop());
        }
    }
    setNamedStrings(e) {
      let t = e["string-set"];
      if (!t) return;
      t = t.filterValue(
        new vo(this, this.currentElement, this.counterResolver)
      );
      let i = t.value instanceof ge ? t.value.values : [t.value];
      for (let e of i)
        if (e instanceof q) {
          let t = e.values[0].stringValue(),
            i = e.values
              .slice(1)
              .map((e) => fi(e))
              .join("");
          this.counterResolver.setNamedString(t, i, this.currentElementOffset);
        }
      delete e["string-set"];
    }
    setRunningElement(e) {
      let t = e.position;
      if (
        (null == t ? void 0 : t.value) instanceof At &&
        "running" === t.value.name
      ) {
        let e = t.value.values[0].stringValue();
        this.counterResolver.setRunningElement(e, this.currentElementOffset);
      }
    }
    processPseudoelementProps(e, t) {
      this.pushCounters(e);
      let i = e.content;
      i && (e.content = i.filterValue(new vo(this, t, this.counterResolver))),
        this.popCounters();
    }
    pushElement(e, t, i, n) {
      var r, s;
      (this.currentPageType = null),
        (this.currentElement = t),
        (this.currentElementOffset = n),
        (this.currentStyle = i),
        (this.currentNamespace = t.namespaceURI),
        (this.currentLocalName = t.localName);
      let o = this.code.nsPrefix[this.currentNamespace];
      (this.currentNSTag = o ? o + this.currentLocalName : ""),
        (this.currentId = t.getAttribute("id")),
        (this.currentXmlId = t.getAttributeNS(
          "http://www.w3.org/XML/1998/namespace",
          "id"
        ));
      let a = t.getAttribute("class");
      this.currentClassNames = a ? a.split(/\s+/) : iu;
      let l = t.getAttributeNS("http://www.idpf.org/2007/ops", "type");
      this.currentEpubTypes = l ? l.split(/\s+/) : iu;
      let h = _o(t);
      h &&
        (this.stack[this.stack.length - 1].push(new Iu(this.lang)),
        (this.lang = h.toLowerCase()));
      let u = this.isRoot,
        c = this.siblingOrderStack;
      (this.currentSiblingOrder = ++c[c.length - 1]), c.push(0);
      let d = this.siblingTypeCountsStack,
        p = (this.currentSiblingTypeCounts = d[d.length - 1]),
        f = p[this.currentNamespace];
      f || (f = p[this.currentNamespace] = {}),
        (f[this.currentLocalName] = (f[this.currentLocalName] || 0) + 1),
        d.push({});
      let g = this.followingSiblingOrderStack;
      null !== g[g.length - 1]
        ? (this.currentFollowingSiblingOrder = --g[g.length - 1])
        : (this.currentFollowingSiblingOrder = null),
        g.push(null);
      let m = this.followingSiblingTypeCountsStack,
        w = (this.currentFollowingSiblingTypeCounts = m[m.length - 1]);
      w &&
        w[this.currentNamespace] &&
        w[this.currentNamespace][this.currentLocalName]--,
        m.push({}),
        this.applyActions(),
        this.applyVarFilter([this.currentStyle], e, t),
        this.applyCalcFilter(this.currentStyle, this.context),
        this.applyAttrFilter(t);
      let v = i.quotes,
        y = null;
      if (v) {
        let e = v.evaluate(this.context);
        e &&
          ((y = new Vu(this.quotes)),
          e === b.none
            ? (this.quotes = [new ue(""), new ue("")])
            : e === b.auto || e === b.initial
            ? (this.quotes = [
                new ue("“"),
                new ue("”"),
                new ue("‘"),
                new ue("’"),
              ])
            : e instanceof q && (this.quotes = e.values));
      }
      this.pushCounters(this.currentStyle);
      let x =
        this.currentId || this.currentXmlId || t.getAttribute("name") || "";
      if (u || x) {
        let e = {};
        Object.keys(this.counters).forEach((t) => {
          e[t] = Array.from(this.counters[t]);
        }),
          this.counterListener.countersOfId(x, e);
      }
      let S = Tt(this.currentStyle, "_pseudos");
      if (S) {
        let e = !0;
        for (let i of LC) {
          i || (e = !1);
          let n = S[i];
          n &&
            ((("before" !== i && "after" !== i) ||
              St(null == (r = n.content) ? void 0 : r.value)) &&
            (("footnote-call" !== i && "footnote-marker" !== i) ||
              (null == (s = he(this.currentStyle, "float"))
                ? void 0
                : s.value) === b.footnote)
              ? e
                ? this.processPseudoelementProps(n, t)
                : this.stack[this.stack.length - 2].push(new Ru(n, t))
              : delete S[i]);
        }
      }
      this.setNamedStrings(this.currentStyle),
        this.setRunningElement(this.currentStyle),
        y && this.stack[this.stack.length - 2].push(y);
    }
    applyAttrFilterInner(e, t) {
      for (let i in t) Ls(i) && !bt(i) && (t[i] = t[i].filterValue(e));
    }
    applyAttrFilter(e) {
      let t = new Fu(e),
        i = this.currentStyle,
        n = Tt(i, "_pseudos");
      for (let e in n) this.applyAttrFilterInner(t, n[e]);
      this.applyAttrFilterInner(t, i);
    }
    applyVarFilter(e, t, i) {
      var n, r, s;
      let o = e[0],
        a = new zu(e, t, i),
        l = {};
      for (let h in o)
        if (lu(h)) {
          let n = Tt(o, h);
          for (let r in n) this.applyVarFilter([n[r], ...e], t, i);
        } else if (Ls(h)) {
          let e = he(o, h),
            i = e.value;
          for (let e = 0; ; e++) {
            if (e >= 32) {
              i = O;
              break;
            }
            let t = i.visit(a);
            if (a.error) {
              (i = O), (a.error = !1);
              break;
            }
            if (t === i) break;
            i = t;
          }
          if (i !== e.value) {
            let a = t.validatorSet,
              u =
                null == (n = null == a ? void 0 : a.shorthands[h])
                  ? void 0
                  : n.clone();
            if (u)
              if (M(i)) {
                for (let t of u.propList) {
                  let n = new _(i, e.priority),
                    r = he(o, t);
                  En(l, t, au(this.context, r, n));
                }
                delete o[h];
              } else {
                let n = sn(t.scope, new De(i.toString(), null), "");
                if (n && (n.visit(u), !u.error)) {
                  for (let t of u.propList) {
                    let i = new _(
                        null !=
                        (s = null != (r = u.values[t]) ? r : a.defaultValues[t])
                          ? s
                          : b.initial,
                        e.priority
                      ),
                      n = he(o, t);
                    En(l, t, au(this.context, n, i));
                  }
                  delete o[h];
                }
              }
            else o[h] = new _(i, e.priority);
          }
          if (l[h]) {
            let e = he(o, h);
            e && e.value !== O && ln(l, h, e, this.context);
          }
        }
      for (let e in l) o[e] = l[e];
    }
    applyCalcFilter(e, t) {
      let i = new Ca(t);
      for (let n in e)
        if (lu(n)) {
          let i = Tt(e, n);
          for (let e in i) this.applyCalcFilter(i[e], t);
        } else if (Ls(n) && !bt(n)) {
          let t = he(e, n),
            r = t.value.visit(i);
          r !== t.value && (e[n] = new _(r, t.priority));
        }
    }
    applyActions() {
      let e;
      for (e = 0; e < this.currentClassNames.length; e++)
        this.applyAction(this.code.classes, this.currentClassNames[e]);
      for (e = 0; e < this.currentEpubTypes.length; e++)
        this.applyAction(this.code.epubtypes, this.currentEpubTypes[e]);
      this.applyAction(this.code.ids, this.currentId),
        this.applyAction(this.code.tags, this.currentLocalName),
        "" != this.currentLocalName && this.applyAction(this.code.tags, "*"),
        this.applyAction(this.code.nstags, this.currentNSTag),
        null !== this.currentPageType &&
          (this.applyAction(this.code.pagetypes, this.currentPageType),
          this.applyAction(this.code.pagetypes, "*")),
        this.stack.push([]);
      for (let t = 1; t >= -1; --t) {
        let i = this.stack[this.stack.length - t - 2];
        for (e = 0; e < i.length; ) i[e].push(this, t) ? i.splice(e, 1) : e++;
      }
      (this.isFirst = !0), (this.isRoot = !1);
    }
    pop() {
      for (let e = 1; e >= -1; --e) {
        let t = this.stack[this.stack.length - e - 2],
          i = 0;
        for (; i < t.length; ) t[i].pop(this, e) ? t.splice(i, 1) : i++;
      }
      this.stack.pop(), (this.isFirst = !1);
    }
    popRule() {
      this.pop();
    }
    popElement(e) {
      this.siblingOrderStack.pop(),
        this.siblingTypeCountsStack.pop(),
        this.followingSiblingOrderStack.pop(),
        this.followingSiblingTypeCountsStack.pop(),
        this.pop(),
        this.popCounters();
    }
  },
  iu = [],
  LC = [
    "before",
    "transclusion-before",
    "footnote-call",
    "footnote-marker",
    "inner",
    "first-letter",
    "first-line",
    "",
    "transclusion-after",
    "after",
  ],
  Du = null;
function Ef(e) {
  Du = e;
}
var Nn = class extends nn {
    constructor(e, t, i, n, r, s, o) {
      super(e, t, o),
        (this.condition = i),
        (this.regionId = r),
        (this.validatorSet = s),
        p(this, "chain", null),
        p(this, "specificity", 0),
        p(this, "elementStyle", null),
        p(this, "conditionCount", 0),
        p(this, "pseudoelement", null),
        p(this, "footnoteContent", !1),
        p(this, "cascade"),
        p(this, "state"),
        p(this, "viewConditionId", null),
        p(this, "insideSelectorRule"),
        p(this, "invalid", !1),
        (this.cascade = n ? n.cascade : Du ? Du.clone() : new Bu()),
        (this.state = 0);
    }
    insertNonPrimary(e) {
      this.cascade.insertInTable(this.cascade.tags, "*", e);
    }
    processChain(e) {
      let t = yf(this.chain, e);
      (t !== e && t.makePrimary(this.cascade)) || this.insertNonPrimary(t);
    }
    isInsideSelectorRule(e) {
      return 0 != this.state && (this.reportAndSkip(e), !0);
    }
    tagSelector(e, t) {
      (!t && !e) ||
        (t && (this.specificity += 1),
        t && e
          ? this.chain.push(new pu(e, t.toLowerCase()))
          : t
          ? this.chain.push(new pa(t.toLowerCase()))
          : this.chain.push(new fu(e)));
    }
    invalidSelector(e) {
      V.warn(e), this.chain.push(new ze("")), this.setInvalid();
    }
    setInvalid() {
      this.invalid = !0;
      for (let e = this; e instanceof Is; e = e.parent) e.parent.invalid = !0;
    }
    classSelector(e) {
      this.pseudoelement
        ? this.invalidSelector(`::${this.pseudoelement} followed by .${e}`)
        : ((this.specificity += 256), this.chain.push(new uu(e)));
    }
    pseudoclassSelector(e, t) {
      if (this.pseudoelement)
        this.invalidSelector(`::${this.pseudoelement} followed by :${e}`);
      else {
        switch (e.toLowerCase()) {
          case "enabled":
            this.chain.push(new Eu());
            break;
          case "disabled":
            this.chain.push(new Su());
            break;
          case "checked":
            this.chain.push(new Nu());
            break;
          case "root":
          case "scope":
            this.chain.push(new bu());
            break;
          case "link":
            this.chain.push(new pa("a")), this.chain.push(new ha("", "href"));
            break;
          case "-adapt-href-epub-type":
          case "href-epub-type":
            if (t && t.length >= 1 && "string" == typeof t[0]) {
              let e = t[0],
                i = new RegExp(`(^|\\s)${pn(e)}($|\\s)`),
                n = t[1];
              this.chain.push(new hu(i, n));
            } else this.chain.push(new ze(""));
            break;
          case "-adapt-footnote-content":
          case "footnote-content":
            this.footnoteContent = !0;
            break;
          case "visited":
          case "active":
          case "hover":
          case "focus":
            this.chain.push(new ze(""));
            break;
          case "lang":
            if (t && 1 == t.length && "string" == typeof t[0]) {
              let e = t[0];
              this.chain.push(
                new Cu(new RegExp(`^${pn(e.toLowerCase())}($|-)`))
              );
            } else this.chain.push(new ze(""));
            break;
          case "nth-child":
          case "nth-last-child":
          case "nth-of-type":
          case "nth-last-of-type": {
            let i = RC[e.toLowerCase()];
            t && 2 == t.length
              ? this.chain.push(new i(t[0], t[1]))
              : this.chain.push(new ze(""));
            break;
          }
          case "first-child":
            this.chain.push(new fa());
            break;
          case "last-child":
            this.chain.push(new Ci(0, 1));
            break;
          case "first-of-type":
            this.chain.push(new mi(0, 1));
            break;
          case "last-of-type":
            this.chain.push(new bi(0, 1));
            break;
          case "only-child":
            this.chain.push(new fa()), this.chain.push(new Ci(0, 1));
            break;
          case "only-of-type":
            this.chain.push(new mi(0, 1)), this.chain.push(new bi(0, 1));
            break;
          case "empty":
            this.chain.push(new yu());
            break;
          case "before":
          case "after":
          case "first-line":
          case "first-letter":
            return void this.pseudoelementSelector(e, t);
          default:
            return void this.invalidSelector(`Unknown pseudo-class :${e}`);
        }
        this.specificity += 256;
      }
    }
    pseudoelementSelector(e, t) {
      switch (e) {
        case "before":
        case "after":
        case "first-line":
        case "first-letter":
        case "footnote-call":
        case "footnote-marker":
        case "inner":
        case "after-if-continues":
          if (this.pseudoelement)
            return void this.invalidSelector(
              `Double pseudo-element ::${this.pseudoelement}::${e}`
            );
          this.pseudoelement = e;
          break;
        case "first-n-lines":
          if (t && 1 == t.length && "number" == typeof t[0]) {
            let i = Math.round(t[0]);
            if (i > 0 && i == t[0]) {
              if (this.pseudoelement)
                return void this.invalidSelector(
                  `Double pseudo-element ::${this.pseudoelement}::${e}`
                );
              this.pseudoelement = `first-${i}-lines`;
              break;
            }
          }
          this.chain.push(new ze(""));
          break;
        case "nth-fragment":
          t && 2 == t.length
            ? (this.viewConditionId = `NFS_${t[0]}_${t[1]}`)
            : this.chain.push(new ze(""));
          break;
        default:
          return void this.invalidSelector(`Unknown pseudo-element ::${e}`);
      }
      this.specificity += 1;
    }
    idSelector(e) {
      (this.specificity += 65536), this.chain.push(new du(e));
    }
    attributeSelector(e, t, i, n) {
      let r;
      switch (
        ((this.specificity += 256), (t = t.toLowerCase()), (n = n || ""), i)
      ) {
        case 0:
          r = new ha(e, t);
          break;
        case 39:
          r = new gu(e, t, n);
          break;
        case 45:
          r =
            !n || n.match(/\s/)
              ? new ze("")
              : new As(e, t, new RegExp(`(^|\\s)${pn(n)}($|\\s)`));
          break;
        case 44:
          r = new As(e, t, new RegExp(`^${pn(n)}($|-)`));
          break;
        case 43:
          r = n ? new As(e, t, new RegExp(`^${pn(n)}`)) : new ze("");
          break;
        case 42:
          r = n ? new As(e, t, new RegExp(`${pn(n)}$`)) : new ze("");
          break;
        case 46:
          r = n ? new As(e, t, new RegExp(pn(n))) : new ze("");
          break;
        case 50:
          if ("supported" != n)
            return void this.invalidSelector(
              `Unsupported :: attr selector op: ${n}`
            );
          r = new mu(e, t);
          break;
        default:
          return void this.invalidSelector(`Unsupported attr selector: ${i}`);
      }
      this.chain.push(r);
    }
    descendantSelector() {
      let e = "d" + ua++;
      this.processChain(new So(new Pu(e, this.viewConditionId, null))),
        (this.chain = [new ze(e)]),
        (this.viewConditionId = null);
    }
    childSelector() {
      let e = "c" + ua++;
      this.processChain(new So(new ku(e, this.viewConditionId, null))),
        (this.chain = [new ze(e)]),
        (this.viewConditionId = null);
    }
    adjacentSiblingSelector() {
      let e = "a" + ua++;
      this.processChain(new So(new Au(e, this.viewConditionId, null))),
        (this.chain = [new ze(e)]),
        (this.viewConditionId = null);
    }
    followingSiblingSelector() {
      let e = "f" + ua++;
      this.processChain(new So(new Lu(e, this.viewConditionId, null))),
        (this.chain = [new ze(e)]),
        (this.viewConditionId = null);
    }
    nextSelector() {
      this.finishChain(),
        (this.pseudoelement = null),
        (this.footnoteContent = !1),
        (this.specificity = 0),
        (this.chain = []);
    }
    startSelectorRule() {
      this.isInsideSelectorRule("E_CSS_UNEXPECTED_SELECTOR") ||
        ((this.state = 1),
        (this.elementStyle = {}),
        (this.pseudoelement = null),
        (this.specificity = 0),
        (this.footnoteContent = !1),
        (this.chain = []),
        (this.invalid = !1));
    }
    error(e, t) {
      super.error(e, t), 1 == this.state && (this.state = 0), this.setInvalid();
    }
    startStylesheet(e) {
      super.startStylesheet(e), (this.state = 0);
    }
    startRuleBody() {
      this.finishChain(),
        super.startRuleBody(),
        1 == this.state && (this.state = 0);
    }
    endRule() {
      super.endRule(), (this.insideSelectorRule = 0);
    }
    finishChain() {
      this.chain &&
        (this.processChain(this.makeApplyRuleAction(this.specificity)),
        (this.chain = null),
        (this.pseudoelement = null),
        (this.viewConditionId = null),
        (this.footnoteContent = !1),
        (this.specificity = 0));
    }
    makeApplyRuleAction(e) {
      let t = this.regionId;
      return (
        this.footnoteContent && (t = t ? "xxx-bogus-xxx" : "footnote"),
        new Rs(
          this.elementStyle,
          e,
          this.pseudoelement,
          t,
          this.viewConditionId
        )
      );
    }
    special(e, t) {
      let i;
      (i = this.condition ? new gi(t, 0, this.condition) : new _(t, 0)),
        bf(this.elementStyle, e).push(i);
    }
    property(e, t, i) {
      this.validatorSet.validatePropertyAndHandleShorthand(e, t, i, this);
    }
    invalidPropertyValue(e, t) {
      this.report(`E_INVALID_PROPERTY_VALUE ${e}: ${t.toString()}`);
    }
    unknownProperty(e, t) {
      this.report(`E_INVALID_PROPERTY ${e}: ${t.toString()}`);
    }
    simpleProperty(e, t, i) {
      "display" == e &&
        (t === b.oeb_page_head || t === b.oeb_page_foot) &&
        (this.simpleProperty(
          "flow-options",
          new q([b.exclusive, b._static]),
          i
        ),
        this.simpleProperty("flow-into", t, i),
        (t = b.block)),
        Ge("SIMPLE_PROPERTY").forEach((n) => {
          let r = n({ name: e, value: t, important: i });
          (e = r.name), (t = r.value), (i = r.important);
        });
      let n =
          (i ? this.getImportantSpecificity() : this.getBaseSpecificity()) +
          this.cascade.nextOrder(),
        r = this.condition ? new gi(t, n, this.condition) : new _(t, n);
      ln(this.elementStyle, e, r);
    }
    finish() {
      return this.cascade;
    }
    startFuncWithSelector(e) {
      let t;
      switch (e) {
        case "is":
          t = new Is(this);
          break;
        case "not":
          t = new Mu(this);
          break;
        case "where":
          t = new _u(this);
          break;
        case "has":
          t = new Uu(this);
      }
      t && (t.startSelectorRule(), this.owner.pushHandler(t));
    }
  },
  RC = {
    "nth-child": xu,
    "nth-of-type": mi,
    "nth-last-child": Ci,
    "nth-last-of-type": bi,
  },
  ua = 0,
  Is = class e extends Nn {
    constructor(e) {
      super(e.scope, e.owner, e.condition, e, e.regionId, e.validatorSet, !1),
        (this.parent = e),
        p(this, "parentChain"),
        p(this, "chains", []),
        p(this, "maxSpecificity", 0),
        p(this, "selectorTexts", []),
        (this.parentChain = e.chain);
    }
    nextSelector() {
      this.chain && this.chains.push(this.chain),
        (this.maxSpecificity = Math.max(this.maxSpecificity, this.specificity)),
        (this.chain = []),
        (this.pseudoelement = null),
        (this.viewConditionId = null),
        (this.footnoteContent = !1),
        (this.specificity = 0);
    }
    endFuncWithSelector() {
      this.chain && this.chains.push(this.chain),
        this.chains.length > 0
          ? ((this.maxSpecificity = Math.max(
              this.maxSpecificity,
              this.specificity
            )),
            this.parentChain.push(
              this.relational()
                ? new wu(this.selectorTexts)
                : this.positive()
                ? new xi(this.chains)
                : new Tu(this.chains)
            ),
            this.increasingSpecificity() &&
              (this.parent.specificity += this.maxSpecificity))
          : this.parentChain.push(new ze("")),
        this.owner.popHandler();
    }
    startRuleBody() {
      this.reportAndSkip("E_CSS_UNEXPECTED_RULE_BODY");
    }
    error(t, i) {
      super.error(t, i),
        (this.chain = null),
        (this.pseudoelement = null),
        (this.viewConditionId = null),
        (this.footnoteContent = !1),
        (this.specificity = 0);
      let n = !1;
      for (let t = this; t instanceof e; t = t.parent)
        if (t.forgiving()) {
          n = !0;
          break;
        }
      n || this.owner.popHandler();
    }
    pushSelectorText(e) {
      this.chain && this.relational() && this.selectorTexts.push(e);
    }
    positive() {
      return !0;
    }
    increasingSpecificity() {
      return !0;
    }
    forgiving() {
      return !0;
    }
    relational() {
      return !1;
    }
  },
  Mu = class extends Is {
    positive() {
      return !1;
    }
    forgiving() {
      return !1;
    }
  },
  _u = class extends Is {
    increasingSpecificity() {
      return !1;
    }
  },
  Uu = class extends Is {
    relational() {
      return !0;
    }
  },
  ga = class extends nn {
    constructor(e, t) {
      super(e, t, !1);
    }
    property(e, t, i) {
      if (this.scope.values[e])
        this.error(`E_CSS_NAME_REDEFINED ${e}`, this.getCurrentToken());
      else {
        let i = e.match(/height|^(top|bottom)$/) ? "vh" : "vw",
          n = new Ot(this.scope, 100, i);
        this.scope.defineName(e, t.toExpr(this.scope, n));
      }
    }
  },
  Vs = class extends nn {
    constructor(e, t, i, n, r, s) {
      super(e, t, !1),
        (this.condition = i),
        (this.elementStyle = n),
        (this.validatorSet = r),
        (this.ruleType = s),
        p(this, "order"),
        (this.order = 0);
    }
    property(e, t, i) {
      i
        ? V.warn("E_IMPORTANT_NOT_ALLOWED")
        : this.validatorSet.validatePropertyAndHandleShorthand(e, t, i, this);
    }
    invalidPropertyValue(e, t) {
      V.warn("E_INVALID_PROPERTY_VALUE", `${e}:`, t.toString());
    }
    unknownProperty(e, t) {
      V.warn("E_INVALID_PROPERTY", `${e}:`, t.toString());
    }
    simpleProperty(e, t, i) {
      let n = i ? this.getImportantSpecificity() : this.getBaseSpecificity();
      (n += this.order), (this.order += Yu);
      let r = this.condition ? new gi(t, n, this.condition) : new _(t, n);
      ln(this.elementStyle, e, r);
    }
  },
  Hu = class extends ti {
    constructor(e, t) {
      super(e),
        (this.validatorSet = t),
        p(this, "elementStyle", {}),
        p(this, "order", 0);
    }
    property(e, t, i) {
      this.validatorSet.validatePropertyAndHandleShorthand(e, t, i, this);
    }
    invalidPropertyValue(e, t) {
      V.warn("E_INVALID_PROPERTY_VALUE", `${e}:`, t.toString());
    }
    unknownProperty(e, t) {
      V.warn("E_INVALID_PROPERTY", `${e}:`, t.toString());
    }
    simpleProperty(e, t, i) {
      let n = i ? Hr : Ur;
      (n += this.order), (this.order += Yu);
      let r = new _(t, n);
      ln(this.elementStyle, e, r);
    }
  };
function qu(e, t) {
  let i = Cf(e);
  i &&
    i.forEach((e) => {
      e.matcher.matches() && t(e.styles);
    });
}
function ma(e, t, i) {
  qu(i, (i) => {
    Po(e, i, t);
  });
}
function Sf(e, t, i, n) {
  let r = new Hu(e, t),
    s = new De(n, r);
  try {
    Nh(s, r, i);
  } catch (e) {
    V.warn(e, "Style attribute parse error:");
  }
  return r.elementStyle;
}
function ya(e, t, i) {
  let n = e["writing-mode"];
  if (n) {
    let e = n.evaluate(t, "writing-mode");
    if (e && e !== b.inherit && e !== b.revert && e !== b.unset)
      return e === b.vertical_rl;
  }
  return i;
}
function Ea(e, t, i) {
  let n = e.direction;
  if (n) {
    let e = n.evaluate(t, "direction");
    if (e && e !== b.inherit && e !== b.revert && e !== b.unset)
      return e === b.rtl;
  }
  return i;
}
function Sa(e, t, i, n, r) {
  let s = {};
  for (let t in e) Ls(t) && (s[t] = he(e, t));
  return (
    ma(s, t, e),
    Ku(e, i, n, (e, i) => {
      Po(s, i, t), ma(s, t, i);
    }),
    s
  );
}
function Ku(e, t, i, n) {
  let r = Tt(e, "_regions");
  if ((t || i) && r) {
    if (i) {
      let e = ["footnote"];
      t = t ? t.concat(e) : e;
    }
    for (let e of t) {
      let t = r[e];
      t && n(e, t);
    }
  }
}
function Po(e, t, i) {
  for (let n in t)
    if (Ls(n)) {
      let r = he(t, n),
        s = e[n];
      e[n] = au(i, s, r);
    }
}
var Na = (e, t, i, n, r, s) => {
    var o, a;
    let l = i ? (n ? xC : Wu) : n ? yC : $u,
      h = r ? EC : SC;
    for (let i in e) {
      let n = e[i];
      if (!n) continue;
      let u,
        c = null != (o = l[i]) ? o : l[h[i]],
        d = null != (a = h[i]) ? a : h[l[i]],
        p = null != c ? c : d;
      if (p) {
        let t = e[p];
        if (
          (t && t.priority > n.priority) ||
          (c &&
            d &&
            c !== d &&
            ((p = d), (t = e[p]), t && t.priority > n.priority))
        )
          continue;
        u = df[c] ? c : df[d] ? d : i;
      } else
        (u = i),
          i.startsWith("text-align") &&
            (n.value === b.inside || n.value === b.outside) &&
            (n = new _(
              r === (n.value === b.inside) ? b.right : b.left,
              n.priority
            ));
      t[u] = s(i, n);
    }
  },
  zu = class extends Jt {
    constructor(e, t, i) {
      super(), (this.elementStyles = e), (this.styler = t), (this.element = i);
    }
    getVarValue(e) {
      var t, i, n, r, s;
      let o = null != (t = this.element) ? t : this.styler.root;
      if (null != (i = this.elementStyles) && i.length) {
        for (let t of this.elementStyles) {
          let i = null == (n = t[e]) ? void 0 : n.value;
          if (i) return i;
        }
        this.element && (o = this.element.parentElement);
      }
      for (; o; o = o.parentElement) {
        let t =
          null ==
          (s = null == (r = this.styler.getStyle(o, !1)) ? void 0 : r[e])
            ? void 0
            : s.value;
        if (t) return t;
      }
      return null;
    }
    visitFunc(e) {
      if ("var" !== e.name) return super.visitFunc(e);
      let t = e.values[0] instanceof be && e.values[0].name;
      return t && bt(t)
        ? this.getVarValue(t) ||
            (e.values.length < 2
              ? ((this.error = !0), O)
              : 2 === e.values.length
              ? e.values[1]
              : new ge(e.values.slice(1)))
        : ((this.error = !0), O);
    }
  },
  Ca = class extends Jt {
    constructor(e, t, i, n) {
      super(),
        (this.context = e),
        (this.resolveViewportUnit = t),
        (this.percentRef = i),
        (this.vertical = n);
    }
    visitFunc(e) {
      let t = super.visitFunc(e);
      if ("calc" !== e.name) return t;
      let i = t.toString().replace(/^calc\b/, "-epubx-expr");
      if (
        /\d(%|em|ex|cap|ch|ic|lh|p?v[whbi]|p?vmin|p?vmax)\W|\Wvar\(\s*--/i.test(
          i
        )
      )
        return t;
      let n = sn(this.context.rootScope, new De(i, null), "");
      if (n instanceof F)
        try {
          let e = n.expr.evaluate(this.context);
          "number" == typeof e &&
            !isNaN(e) &&
            (/\d(px|in|pt|pc|cm|mm|q|rem|rlh)\W/i.test(i)
              ? (t = new P(e, "px"))
              : /\d[a-z]/i.test(i) || (t = new nt(e)));
        } catch (e) {
          V.warn(e);
        }
      return t;
    }
    visitNumeric(e) {
      return this.resolveViewportUnit && (Ml(e.unit) || Jp(e.unit))
        ? new P(
            e.num * this.context.queryUnitSize(e.unit, !1, this.vertical),
            "px"
          )
        : "number" == typeof this.percentRef && "%" === e.unit
        ? new P((e.num * this.percentRef) / 100, "px")
        : e;
    }
  };
function Ei(e, t, i, n, r) {
  try {
    if (t instanceof F)
      return t.expr instanceof Z &&
        (t.expr.str.startsWith("named-string-") ||
          t.expr.str.startsWith("running-element-"))
        ? t
        : vh(e, t.expr, i);
    if (t instanceof P || t instanceof At || t instanceof q || t instanceof ge)
      return t.visit(new Ca(e, !0, n, r));
  } catch (e) {
    return V.warn(e), O;
  }
  return t;
}
var Nf = { first: !1, forceEnd: !1, allowEnd: !1, last: !1 };
function vf(e) {
  let t = e instanceof Se ? e : "string" == typeof e ? L(e) : b.none;
  if (t === b.none) return Nf;
  let i = t instanceof q ? t.values : [t],
    n = Object.create(Nf);
  for (let e of i)
    if (e instanceof be)
      switch (e.name) {
        case "first":
          n.first = !0;
          break;
        case "force-end":
          (n.forceEnd = !0), (n.allowEnd = !1);
          break;
        case "allow-end":
          (n.forceEnd = !1), (n.allowEnd = !0);
          break;
        case "last":
          n.last = !0;
      }
  return n;
}
function Tf(e) {
  return !(e.first || e.last || e.forceEnd || e.allowEnd);
}
var IC = {
    trimStart: !1,
    spaceFirst: !1,
    trimEnd: !1,
    allowEnd: !1,
    trimAdjacent: !1,
  },
  wf = {
    trimStart: !1,
    spaceFirst: !1,
    trimEnd: !1,
    allowEnd: !0,
    trimAdjacent: !0,
  },
  Pf = {
    trimStart: !0,
    spaceFirst: !1,
    trimEnd: !0,
    allowEnd: !1,
    trimAdjacent: !0,
  };
function kf(e) {
  let t = e instanceof Se ? e : "string" == typeof e ? L(e) : b.normal;
  if (t === b.normal) return wf;
  if (t === b.auto) return Pf;
  let i = t instanceof q ? t.values : [t],
    n = Object.create(wf);
  for (let e of i)
    if (e instanceof be)
      switch (e.name) {
        case "trim-both":
        case "trim-auto":
          return Pf;
        case "space-all":
          return IC;
        case "trim-start":
          (n.trimStart = !0), (n.spaceFirst = !1);
          break;
        case "space-start":
          (n.trimStart = !1), (n.spaceFirst = !1);
          break;
        case "space-first":
          (n.trimStart = !1), (n.spaceFirst = !0);
          break;
        case "trim-end":
          (n.trimEnd = !0), (n.allowEnd = !1);
          break;
        case "space-end":
          (n.trimEnd = !1), (n.allowEnd = !1);
          break;
        case "allow-end":
          (n.trimEnd = !1), (n.allowEnd = !0);
          break;
        case "trim-adjacent":
          n.trimAdjacent = !0;
          break;
        case "space-adjacent":
          n.trimAdjacent = !1;
      }
  return n;
}
var Zu = { ideographAlpha: !1, ideographNumeric: !1 },
  VC = { ideographAlpha: !0, ideographNumeric: !0 };
function Af(e) {
  let t = e instanceof Se ? e : "string" == typeof e ? L(e) : b.normal;
  if (t === b.normal || t === b.auto) return VC;
  if (t === b.none) return Zu;
  let i = t instanceof q ? t.values : [t],
    n = Object.create(Zu);
  for (let e of i)
    if (e instanceof be)
      switch (e.name) {
        case "no-autospace":
          return Zu;
        case "ideograph-alpha":
          n.ideographAlpha = !0;
          break;
        case "ideograph-numeric":
          n.ideographNumeric = !0;
      }
  return n;
}
function Lf(e, t) {
  return !(
    e.ideographAlpha ||
    e.ideographNumeric ||
    t.trimStart ||
    t.spaceFirst ||
    t.trimEnd ||
    t.allowEnd ||
    t.trimAdjacent
  );
}
function Rf(e) {
  return e
    ? ((e = e.toLowerCase()),
      /^zh\b.*-(hant|tw|hk)\b/.test(e)
        ? "zh-hant"
        : /^zh\b/.test(e)
        ? "zh-hans"
        : /^ja\b/.test(e)
        ? "ja"
        : /^ko\b/.test(e)
        ? "ko"
        : e)
    : null;
}
var Qu = class {
    getPolyfilledInheritedProps() {
      return ["hanging-punctuation", "text-autospace", "text-spacing-trim"];
    }
    preprocessSingleDocument(e) {
      e.body && this.preprocessForTextSpacing(e.body);
    }
    preprocessForTextSpacing(e) {
      var t;
      let i = e.ownerDocument.createNodeIterator(e, NodeFilter.SHOW_TEXT);
      for (let e = i.nextNode(); e; e = i.nextNode()) {
        if (
          "http://www.w3.org/1999/xhtml" !== e.parentElement.namespaceURI ||
          "true" ===
            (null == (t = e.parentElement.dataset) ? void 0 : t.mathTypeset)
        )
          continue;
        let i = e.textContent
          .replace(
            /(?![()\[\]{}])[\p{Ps}\p{Pe}\p{Pf}\p{Pi}、。，．：；､｡\u3000]\p{M}*(?=\P{M})|.(?=(?![()\[\]{}])[\p{Ps}\p{Pe}\p{Pf}\p{Pi}、。，．：；､｡\u3000])|(?!\p{P})[\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF]\p{M}*(?=(?![\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF\uFF01-\uFF60])[\p{L}\p{Nd}])|(?![\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF\uFF01-\uFF60])[\p{L}\p{Nd}]\p{M}*(?=(?!\p{P})[\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF])/gsu,
            "$&\0"
          )
          .split("\0");
        if (i.length > 1) {
          let t = i.length - 1;
          for (let n = 0; n < t; n++)
            e.parentNode.insertBefore(document.createTextNode(i[n]), e);
          e.textContent = i[t];
        }
      }
    }
    processGeneratedContent(e, t, i, n, r, s) {
      r = Rf(r);
      let o = Af(t),
        a = kf(i),
        l = vf(n);
      if (Tf(l) && Lf(o, a)) return;
      this.preprocessForTextSpacing(e);
      let h = e.style.whiteSpace;
      0 === (s ? e.offsetHeight : e.offsetWidth) &&
        (e.style.whiteSpace = "pre");
      let u = e.ownerDocument.createNodeIterator(e, NodeFilter.SHOW_TEXT),
        c = null,
        d = null;
      for (let e = u.nextNode(); e; e = d) {
        d = u.nextNode();
        let t = !c,
          i = !c || /\n$/.test(c.textContent),
          n = !d || /^\n/.test(d.textContent),
          h = !d;
        this.processTextSpacing(e, t || i, t, i, n, h, c, d, o, a, l, r, s),
          (c = e);
      }
      e.style.whiteSpace = h;
    }
    postLayoutBlock(e, t) {
      var i, n, r, s, o, a, l;
      let h =
          !e ||
          (1 === e.fragmentIndex &&
            (function () {
              let e = t[0];
              for (let t = e; ; t = t.parent)
                if (!t || !t.inline) {
                  if (1 !== (null == t ? void 0 : t.fragmentIndex)) return !1;
                  break;
                }
              if (!e.inline) return !0;
              for (let t = e.viewNode.previousSibling; t; t = t.previousSibling)
                if (!de(t, e.whitespace) && !c(t)) return !1;
              return !0;
            })()),
        u =
          h ||
          (function () {
            var e, i;
            let n,
              r = t[0];
            for (
              ;
              r &&
              r.inline &&
              ((n = null == (e = r.viewNode) ? void 0 : e.previousSibling),
              !n ||
                (3 === n.nodeType &&
                  /^[ \t\r\n\f]*$/.test(n.textContent) &&
                  r.whitespace !== Ze.PRESERVE &&
                  (n = n.previousSibling),
                !n));

            )
              r = r.parent;
            for (; n; ) {
              if (1 === n.nodeType) {
                if ("br" === n.localName) return !0;
                let e = null == (i = n.style) ? void 0 : i.display;
                if (e && "inline" !== e) return !/^(inline|ruby)\b/.test(e);
              } else if (3 === n.nodeType)
                if (r.whitespace === Ze.PRESERVE) {
                  if (/\n$/.test(n.textContent)) return !0;
                } else if (
                  r.whitespace === Ze.NEWLINE &&
                  /\n[ \t\r\n\f]*$/.test(n.textContent)
                )
                  return !0;
              n = n.lastChild;
            }
            return !1;
          })();
      function c(e) {
        var t;
        if (1 !== (null == e ? void 0 : e.nodeType)) return !1;
        let i = e;
        if (i.hasAttribute(Ht)) return !0;
        let { position: n, float: r } = null != (t = i.style) ? t : {};
        return "absolute" === n || "fixed" === n || (r && "none" !== r);
      }
      let d = -1;
      for (let p = 0; p < t.length; p++) {
        let f = t[p];
        if (
          !f.after &&
          f.inline &&
          !f.display &&
          f.parent &&
          f.viewNode.parentNode &&
          f.viewNode.nodeType === Node.TEXT_NODE &&
          !de(f.viewNode, f.whitespace)
        ) {
          let g = function (e) {
              var t, i, n;
              if (1 === (null == (t = e.viewNode) ? void 0 : t.nodeType))
                return "br" === e.viewNode.localName;
              if (3 === (null == (i = e.viewNode) ? void 0 : i.nodeType)) {
                if (e.whitespace === Ze.PRESERVE) {
                  if (/\n$/.test(e.viewNode.textContent)) return !0;
                } else if (
                  e.whitespace === Ze.NEWLINE &&
                  /\n[ \t\r\n\f]*$/.test(e.viewNode.textContent)
                )
                  return !0;
                if (
                  "br" ===
                  (null == (n = e.viewNode.previousElementSibling)
                    ? void 0
                    : n.localName)
                )
                  return de(e.viewNode, e.whitespace);
              }
              return !1;
            },
            m = function (e) {
              var t, i, n;
              if (1 === (null == (t = e.viewNode) ? void 0 : t.nodeType))
                return "br" === e.viewNode.localName;
              if (3 === (null == (i = e.viewNode) ? void 0 : i.nodeType)) {
                if (e.whitespace === Ze.PRESERVE) {
                  if (/^\n/.test(e.viewNode.textContent)) return !0;
                } else if (
                  e.whitespace === Ze.NEWLINE &&
                  /^[ \t\r\n\f]*\n/.test(e.viewNode.textContent)
                )
                  return !0;
                if (
                  "br" ===
                  (null == (n = e.viewNode.nextElementSibling)
                    ? void 0
                    : n.localName)
                )
                  return de(e.viewNode, e.whitespace);
              }
              return !1;
            };
          let w = Rf(
              null !=
                (s =
                  null != (n = null != (i = f.lang) ? i : f.parent.lang)
                    ? n
                    : null == e
                    ? void 0
                    : e.lang)
                ? s
                : null == (r = null == e ? void 0 : e.parent)
                ? void 0
                : r.lang
            ),
            b = Af(f.inheritedProps["text-autospace"]),
            v = kf(f.inheritedProps["text-spacing-trim"]),
            y = vf(f.inheritedProps["hanging-punctuation"]);
          if ((Tf(y) && Lf(b, v)) || /\b(flex|grid)\b/.test(f.parent.display))
            continue;
          d < 0 && (d = p);
          let x = null,
            S = null,
            C = p === d,
            E = p === d && h,
            k = p === d && u,
            P = !1,
            N = !1;
          for (let e = p - 1; e >= 0; e--) {
            let i = t[e];
            if (g(i)) {
              k = !0;
              break;
            }
            if (
              !i.display &&
              i.viewNode.nodeType === Node.TEXT_NODE &&
              i.viewNode.textContent.length > 0
            ) {
              x = i.viewNode;
              break;
            }
            if (
              (i.display && !/^(inline|ruby)\b/.test(i.display)) ||
              (1 === (null == (o = i.viewNode) ? void 0 : o.nodeType) &&
                ("br" === i.viewNode.localName || Zt[i.viewNode.localName]))
            )
              break;
            0 === e && ((C = !0), h && ((E = !0), (k = !0)));
          }
          for (let e = p + 1; e < t.length; e++) {
            let i = t[e];
            if (m(i)) {
              P = !0;
              break;
            }
            if (
              i.viewNode !== f.viewNode &&
              !i.display &&
              i.viewNode.nodeType === Node.TEXT_NODE &&
              i.viewNode.textContent.length > 0
            ) {
              S = i.viewNode;
              break;
            }
            if (
              (i.display && !/^(inline|ruby)\b/.test(i.display)) ||
              (1 === (null == (a = i.viewNode) ? void 0 : a.nodeType) &&
                ("br" === i.viewNode.localName || Zt[i.viewNode.localName]))
            ) {
              e === t.length - 1 && c(i.viewNode) && (N = !0);
              break;
            }
            if (e === t.length - 1) {
              (P = !0), (N = !0);
              for (let e = i.viewNode.nextSibling; e; e = e.nextSibling)
                if (!c(e)) {
                  N = !1;
                  break;
                }
            }
          }
          if (
            "inline-block" === (null == (l = f.parent) ? void 0 : l.display)
          ) {
            if (!E) {
              let e = f.parent.viewNode.firstChild;
              for (; de(e, f.whitespace); ) e = e.nextSibling;
              f.viewNode === e && (E = !0);
            }
            if (!N) {
              let e = f.parent.viewNode.lastChild;
              for (; de(e, f.whitespace); ) e = e.previousSibling;
              f.viewNode === e && (N = !0);
            }
          }
          if (
            this.processTextSpacing(
              f.viewNode,
              C,
              E,
              k,
              P,
              N,
              x,
              S,
              b,
              v,
              y,
              w,
              f.vertical
            ) > 0
          )
            break;
        }
      }
    }
    processTextSpacing(e, t, i, n, r, s, o, a, l, h, u, c, d) {
      let p,
        f,
        g,
        m = e.textContent,
        w = e.ownerDocument,
        b = 0;
      function v() {
        if (t) return !0;
        if (!o) return !1;
        p || ((p = w.createRange()), p.selectNode(e));
        let i = p.getClientRects()[0];
        if (!i) return !1;
        f || ((f = w.createRange()), f.selectNode(o));
        let n = f.getClientRects(),
          r = n[n.length - 1];
        return (
          !!r &&
          (b || (b = bo(r, d)),
          d
            ? i.top < r.top + r.height - i.width ||
              i.left + i.width < r.left + i.width / 10 ||
              i.left > r.left + r.width - i.width / 10
            : i.left < r.left + r.width - i.height ||
              i.top > r.top + r.height - i.height / 10 ||
              i.top + i.height < r.top + i.height / 10)
        );
      }
      function y() {
        if (!a) return !1;
        p || ((p = w.createRange()), p.selectNode(e));
        let t = p.getClientRects()[0];
        if (!t) return !1;
        b || (b = bo(t, d)), g || ((g = w.createRange()), g.selectNode(a));
        let i = g.getClientRects()[0];
        return (
          !!i &&
          (d
            ? t.top + t.height > i.top + t.width ||
              t.left > i.left + i.width - t.width / 10 ||
              t.left + t.width < i.left + t.width / 10
            : t.left + t.width > i.left + t.height ||
              t.top + t.height < i.top + t.height / 10 ||
              t.top > i.top + i.height - t.height / 10)
        );
      }
      let x,
        S = !1,
        C = !1,
        E = !1,
        k = !1;
      if (
        (i && u.first && /^[\p{Ps}\p{Pf}\p{Pi}'"\u3000]\p{M}*$/u.test(m)
          ? ((x = "viv-ts-open"), (S = !0), (C = !0))
          : s && u.last && /^[\p{Pe}\p{Pf}\p{Pi}'"]\p{M}*$/u.test(m)
          ? ((x = "viv-ts-close"), (S = !0), (E = !0))
          : (u.forceEnd || u.allowEnd) && /^[、。，．､｡]\p{M}*$/u.test(m)
          ? ((x = "viv-ts-close"), (S = !0), (k = !0))
          : (h.trimStart || h.spaceFirst || h.trimAdjacent) &&
            /^[‘“〝（［｛｟〈〈《「『【〔〖〘〚]\p{M}*$/u.test(m)
          ? ((x = "viv-ts-open"), (S = !0))
          : (h.trimEnd || h.allowEnd || h.trimAdjacent) &&
            (/^[’”〞〟）］｝｠〉〉》」』】〕〗〙〛]\p{M}*$/u.test(m) ||
              ("zh-hans" === c && /^[：；]\p{M}*$/u.test(m)) ||
              ("zh-hant" !== c && /^[、。，．]\p{M}*$/u.test(m))) &&
            ((x = "viv-ts-close"), (S = !0)),
        S)
      ) {
        let t = function (e) {
            let t =
              0.7 * parseFloat(w.defaultView.getComputedStyle(e).fontSize);
            return (d ? e.offsetHeight : e.offsetWidth) > t;
          },
          l = function () {
            return d ? c.offsetLeft : c.offsetTop;
          };
        if ("viv-ts-inner" === e.parentElement.localName) return 0;
        let c = w.createElement(x),
          p = w.createElement("viv-ts-inner");
        c.appendChild(p), e.parentNode.insertBefore(c, e), p.appendChild(e);
        let f = t(p);
        if (f || C || E || k)
          if ("viv-ts-open" === x)
            if (C) c.className = "viv-hang-first";
            else if (i || n)
              h.trimStart
                ? (c.className = "viv-ts-trim")
                : (c.className = "viv-ts-space");
            else if (h.trimStart || h.spaceFirst || !v()) {
              if (
                h.trimAdjacent &&
                o &&
                /[\p{Ps}\p{Pi}\p{Pe}\p{Pf}\u00B7\u2027\u30FB\u3000：；、。，．]\p{M}*$/u.test(
                  o.textContent
                ) &&
                (!/[\p{Pe}\p{Pf}]\p{M}*$/u.test(o.textContent) ||
                  ("viv-ts-inner" === o.parentElement.localName &&
                    t(o.parentElement)))
              )
                c.className = "viv-ts-trim";
              else if ((h.trimStart || h.spaceFirst) && v()) {
                let e = l();
                (c.className = "viv-ts-auto"),
                  e === l() && !v() && (c.className = "viv-ts-trim");
              }
            } else c.className = "viv-ts-space";
          else if ("viv-ts-close" === x)
            if (E)
              c.className = f ? "viv-hang-last" : "viv-hang-last viv-hang-hw";
            else if (s || r) {
              let e = l();
              k
                ? ((c.className = f
                    ? u.allowEnd && h.allowEnd
                      ? "viv-ts-auto"
                      : "viv-hang-end"
                    : "viv-hang-end viv-hang-hw"),
                  u.allowEnd &&
                    e === l() &&
                    (h.trimEnd
                      ? (c.className = "viv-ts-trim")
                      : h.allowEnd
                      ? ((c.className = "viv-hang-end"),
                        e === l() && (c.className = ""))
                      : (c.className = "viv-ts-space")))
                : h.trimEnd
                ? (c.className = "viv-ts-trim")
                : h.allowEnd
                ? ((c.className = "viv-ts-auto"),
                  e === l() && (c.className = ""))
                : (c.className = "viv-ts-space");
            } else if (
              a &&
              /^[\p{Pe}\p{Pf}\u00B7\u2027\u30FB\u3000：；、。，．]/u.test(
                a.textContent
              )
            )
              f && h.trimAdjacent && (c.className = "viv-ts-trim");
            else if (k) {
              let e = y(),
                t = e && u.allowEnd;
              t ||
                (c.className = f ? "viv-hang-end" : "viv-hang-end viv-hang-hw"),
                f
                  ? t && h.trimEnd
                    ? (c.className = "viv-ts-auto")
                    : t || y()
                    ? !e &&
                      u.allowEnd &&
                      (h.trimEnd
                        ? ((c.className = "viv-ts-auto"),
                          y() || (c.className = "viv-hang-end"))
                        : ((c.className = "viv-ts-space"),
                          y() ||
                            (h.allowEnd
                              ? ((c.className = "viv-ts-auto"),
                                y() || (c.className = "viv-hang-end"))
                              : (c.className = "viv-hang-end"))))
                    : (c.className = "")
                  : !e && !y() && (c.className = "");
            } else if (h.trimEnd || h.allowEnd)
              if (y())
                h.allowEnd
                  ? (c.className = "viv-ts-space")
                  : (c.className = "viv-ts-auto");
              else {
                let e = l();
                (c.className = "viv-ts-auto"), e === l() && (c.className = "");
              }
      }
      let P = !1;
      function N(e) {
        var t;
        let i =
          null == (t = null == e ? void 0 : e.ownerDocument.defaultView)
            ? void 0
            : t.getComputedStyle(e);
        return (
          !!i &&
          ("upright" === i.textOrientation ||
            "all" === i.textCombineUpright ||
            "horizontal" === i["-webkit-text-combine"])
        );
      }
      function T(e, t) {
        if (1 === e.nodeType) {
          let t = w.defaultView.getComputedStyle(e);
          if (
            parseFloat(t.marginInlineEnd) ||
            parseFloat(t.borderInlineEndWidth) ||
            parseFloat(t.paddingInlineEnd)
          )
            return !0;
        }
        let i = e.parentElement;
        if (i && !i.contains(t)) return T(i, t);
        if (1 === t.nodeType) {
          let e = w.defaultView.getComputedStyle(t);
          if (
            parseFloat(e.marginInlineStart) ||
            parseFloat(e.borderInlineStartWidth) ||
            parseFloat(e.paddingInlineStart)
          )
            return !0;
        }
        let n = t.parentElement;
        return !(!n || n.contains(e)) && T(e, n);
      }
      return (
        (l.ideographAlpha || l.ideographNumeric) &&
          (o &&
            /^(?!\p{P})[\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF]/u.test(m) &&
            ((l.ideographAlpha &&
              /(?![\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF\uFF01-\uFF60])\p{L}\p{M}*$/u.test(
                o.textContent
              )) ||
              (l.ideographNumeric &&
                /(?![\uFF01-\uFF60])\p{Nd}\p{M}*$/u.test(o.textContent))) &&
            !(d && N(o.parentElement)) &&
            !T(o, e) &&
            (e.parentNode.insertBefore(w.createElement("viv-ts-thin-sp"), e),
            (P = !0)),
          a &&
            /(?!\p{P})[\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF]\p{M}*$/u.test(m) &&
            ((l.ideographAlpha &&
              /^(?![\p{sc=Han}\u3041-\u30FF\u31C0-\u31FF\uFF01-\uFF60])\p{L}/u.test(
                a.textContent
              )) ||
              (l.ideographNumeric &&
                /^(?![\uFF01-\uFF60])\p{Nd}/u.test(a.textContent))) &&
            !(d && N(a.parentElement)) &&
            !T(e, a) &&
            (e.parentNode.insertBefore(
              w.createElement("viv-ts-thin-sp"),
              e.nextSibling
            ),
            (P = !0))),
        b
      );
    }
    registerHooks() {
      Ue(
        "POLYFILLED_INHERITED_PROPS",
        this.getPolyfilledInheritedProps.bind(this)
      ),
        Ue(
          "PREPROCESS_SINGLE_DOCUMENT",
          this.preprocessSingleDocument.bind(this)
        ),
        Ue("POST_LAYOUT_BLOCK", this.postLayoutBlock.bind(this), !0);
    }
  },
  Ju = new Qu();
function If(e) {
  Ju.preprocessForTextSpacing(e);
}
function Vf(e, t, i, n, r, s) {
  Ju.processGeneratedContent(e, t, i, n, r, s);
}
Ju.registerHooks();
var ko = new DOMParser().parseFromString(
    '<root xmlns="http://www.pyroxy.com/ns/shadow"/>',
    "text/xml"
  ),
  Bf = [
    "footnote-marker",
    "first-5-lines",
    "first-4-lines",
    "first-3-lines",
    "first-2-lines",
    "first-line",
    "first-letter",
    "before",
    "",
    "after",
  ],
  va = "data-adapt-pseudo";
function Fs(e) {
  return e.getAttribute(va) || "";
}
function Ni(e, t) {
  e.setAttribute(va, t);
}
var Si = class {
    constructor(e, t, i, n, r) {
      (this.element = e),
        (this.style = t),
        (this.styler = i),
        (this.context = n),
        (this.exprContentListener = r),
        p(this, "contentProcessed", {});
    }
    getStyle(e, t) {
      let i = Fs(e);
      this.styler &&
        i &&
        i.match(/after$/) &&
        ((this.style = this.styler.getStyle(this.element, !0)),
        (this.styler = null));
      let n = Tt(this.style, "_pseudos")[i] || {};
      if (i.match(/^first-/) && !n["x-first-pseudo"]) {
        let e,
          t = 1;
        "first-letter" == i
          ? (t = 0)
          : null != (e = i.match(/^first-([0-9]+)-lines$/)) && (t = e[1] - 0),
          (n["x-first-pseudo"] = new _(new ut(t), 0));
      }
      return n;
    }
    processContent(e, t, i) {
      let n = Fs(e);
      if (!this.contentProcessed[n]) {
        this.contentProcessed[n] = !0;
        let i = t.content;
        i &&
          St(i) &&
          (i.visit(new mn(e, this.context, i, this.exprContentListener)),
          If(e));
      }
    }
  },
  Mx = si.isInstanceOfAfterIfContinuesLayoutConstraint,
  Mf = qn.registerFragmentIndex,
  _x = qn.clearFragmentIndices,
  Ta = class {
    constructor(e, t) {
      (this.sourceNode = e), (this.styler = t);
    }
    createElement(e, t) {
      let i = t.viewNode.ownerDocument.createElement("div"),
        n = new Ds(e, i, t),
        r = n.getColumn().pageBreakType;
      return (
        (n.getColumn().pageBreakType = null),
        n
          .layout(this.createNodePositionForPseudoElement(), !0)
          .thenAsync(() => {
            (this.styler.contentProcessed["after-if-continues"] = !1),
              (n.getColumn().pageBreakType = r);
            let e = i.firstChild;
            return w(e, "display", "block"), T(e);
          })
      );
    }
    createNodePositionForPseudoElement() {
      let e = ko.createElementNS("http://www.w3.org/1999/xhtml", "div");
      Ni(e, "after-if-continues");
      let t = this.createShadowContext(e),
        i = {
          steps: [
            {
              node: e,
              shadowType: t.type,
              shadowContext: t,
              nodeShadow: null,
              shadowSibling: null,
            },
          ],
          offsetInNode: 0,
          after: !1,
          preprocessedTextContent: null,
        };
      return new ht(i);
    }
    createShadowContext(e) {
      return new Ns(
        this.sourceNode,
        e,
        null,
        null,
        null,
        Rt.ShadowType.ROOTED,
        this.styler
      );
    }
  },
  ed = class e {
    constructor(e, t, i) {
      (this.nodeContext = e),
        (this.afterIfContinues = t),
        (this.pseudoElementHeight = i),
        p(this, "flagmentLayoutConstraintType", "AfterIfContinue");
    }
    allowLayout(e, t, i) {
      return !((t && !e) || (e && e.overflow));
    }
    nextCandidate(e) {
      return !1;
    }
    postLayout(e, t, i, n) {}
    finishBreak(e, t) {
      return this.getRepetitiveElements().affectTo(e)
        ? this.afterIfContinues
            .createElement(t, this.nodeContext)
            .thenAsync((e) => (this.nodeContext.viewNode.appendChild(e), T(!0)))
        : T(!0);
    }
    getRepetitiveElements() {
      return new td(this.nodeContext, this.pseudoElementHeight);
    }
    equalsTo(t) {
      return t instanceof e && this.afterIfContinues == t.afterIfContinues;
    }
    getPriorityOfFinishBreak() {
      return 9;
    }
  },
  td = class {
    constructor(e, t) {
      (this.nodeContext = e), (this.pseudoElementHeight = t);
    }
    calculateOffset(e) {
      return this.affectTo(e) ? this.pseudoElementHeight : 0;
    }
    calculateMinimumOffset(e) {
      return this.calculateOffset(e);
    }
    affectTo(e) {
      if (!e) return !1;
      let t = e.shadowContext ? e.shadowContext.owner : e.sourceNode;
      if (t === this.nodeContext.sourceNode) return !!e.after;
      for (let e = t.parentNode; e; e = e.parentNode)
        if (e === this.nodeContext.sourceNode) return !0;
      return !1;
    }
  };
function _f(e, t) {
  if (!e || !e.afterIfContinues || e.after || t.isFloatNodeContext(e))
    return T(e);
  let i = e.afterIfContinues;
  return i.createElement(t, e).thenAsync((n) => {
    let r = BC(e, t, n);
    return t.fragmentLayoutConstraints.push(new ed(e, i, r)), T(e);
  });
}
function id(e, t) {
  return e.thenAsync((e) => _f(e, t));
}
function FC(e, t) {
  let i = A("processAfterIfContinuesOfAncestors"),
    n = e;
  return (
    i
      .loop(() => {
        if (null !== n) {
          let e = _f(n, t);
          return (n = n.parent), e.thenReturn(!0);
        }
        return T(!1);
      })
      .then(() => {
        i.finish(!0);
      }),
    i.result()
  );
}
function BC(e, t, i) {
  let n = e.viewNode;
  n.appendChild(i);
  let r = pi(i, t, e.vertical);
  return n.removeChild(i), r;
}
var wa = class {
    constructor(e) {
      this.constraints = e;
    }
    allowLayout(e) {
      return this.constraints.every((t) => t.allowLayout(e));
    }
  },
  Bs = class extends ws {
    constructor(e, t) {
      super(),
        (this.checkPoints = e),
        (this.penalty = t),
        p(this, "alreadyEvaluated", !1),
        p(this, "breakNodeContext", null);
    }
    findAcceptableBreak(e, t) {
      return t < this.getMinBreakPenalty()
        ? null
        : (this.alreadyEvaluated ||
            ((this.breakNodeContext = e.findBoxBreakPosition(this, t > 0)),
            (this.alreadyEvaluated =
              !!this.breakNodeContext || t > 0 || !!e.pseudoParent)),
          this.breakNodeContext);
    }
    getMinBreakPenalty() {
      return this.penalty;
    }
    getNodeContext() {
      return this.alreadyEvaluated
        ? this.breakNodeContext
        : this.checkPoints[this.checkPoints.length - 1];
    }
  },
  Os = class extends mo {
    constructor(e, t, i, n, r) {
      super(e),
        (this.layoutContext = t),
        (this.clientLayout = i),
        (this.layoutConstraint = n),
        (this.pageFloatLayoutContext = r),
        p(this, "last"),
        p(this, "viewDocument"),
        p(this, "flowRootFormattingContext", null),
        p(this, "isFloat", !1),
        p(this, "isFootnote", !1),
        p(this, "startEdge", 0),
        p(this, "endEdge", 0),
        p(this, "beforeEdge", 0),
        p(this, "afterEdge", 0),
        p(this, "footnoteEdge", 0),
        p(this, "box", null),
        p(this, "chunkPositions", null),
        p(this, "bands", null),
        p(this, "overflown", !1),
        p(this, "breakPositions", null),
        p(this, "pageBreakType", null),
        p(this, "forceNonfitting", !0),
        p(this, "leftFloatEdge", 0),
        p(this, "rightFloatEdge", 0),
        p(this, "bottommostFloatTop", 0),
        p(this, "stopAtOverflow", !0),
        p(this, "lastAfterPosition", null),
        p(this, "fragmentLayoutConstraints", []),
        p(this, "pseudoParent", null),
        p(this, "nodeContextOverflowingDueToRepetitiveElements", null),
        p(this, "blockDistanceToBlockEndFloats", NaN),
        p(this, "breakAtTheEdgeBeforeFloat", null),
        e.setAttribute("data-vivliostyle-column", "true"),
        (this.last = e.lastChild),
        (this.viewDocument = e.ownerDocument),
        r.setContainer(this);
    }
    getTopEdge() {
      return this.vertical
        ? this.rtl
          ? this.endEdge
          : this.startEdge
        : this.beforeEdge;
    }
    getBottomEdge() {
      return this.vertical
        ? this.rtl
          ? this.startEdge
          : this.endEdge
        : this.afterEdge;
    }
    getLeftEdge() {
      return this.vertical
        ? this.afterEdge
        : this.rtl
        ? this.endEdge
        : this.startEdge;
    }
    getRightEdge() {
      return this.vertical
        ? this.beforeEdge
        : this.rtl
        ? this.startEdge
        : this.endEdge;
    }
    isFloatNodeContext(e) {
      return !(!e.floatSide || (this.isFloat && !e.parent));
    }
    stopByOverflow(e) {
      return this.stopAtOverflow && !!e && e.overflow;
    }
    isOverflown(e) {
      return this.vertical ? e < this.footnoteEdge : e > this.footnoteEdge;
    }
    getExclusions() {
      let e = this.pageFloatLayoutContext.getFloatFragmentExclusions();
      return this.exclusions.concat(e);
    }
    openAllViews(e) {
      let t = A("openAllViews"),
        i = e.steps;
      this.layoutContext.setViewRoot(this.element, this.isFootnote);
      let n = i.length - 1,
        r = null;
      return (
        t
          .loop(() => {
            for (; n >= 0; ) {
              let t = r,
                s = i[n];
              if (
                ((r = Co(s, t)),
                n === i.length - 1 &&
                  !r.formattingContext &&
                  (r.formattingContext = this.flowRootFormattingContext),
                0 == n &&
                  ((r.offsetInNode =
                    this.calculateOffsetInNodeForNodeContext(e)),
                  (r.after = e.after),
                  (r.preprocessedTextContent = e.preprocessedTextContent),
                  r.after))
              )
                break;
              let o = this.layoutContext.setCurrent(
                r,
                0 == n && 0 == r.offsetInNode
              );
              if ((n--, o.isPending())) return o;
            }
            return T(!1);
          })
          .then(() => {
            t.finish(r);
          }),
        t.result()
      );
    }
    calculateOffsetInNodeForNodeContext(e) {
      return e.preprocessedTextContent
        ? Hh(e.preprocessedTextContent, e.offsetInNode)
        : e.offsetInNode;
    }
    maybePeelOff(e, t) {
      var i, n;
      if (
        e.firstPseudo &&
        e.inline &&
        !e.after &&
        0 == e.firstPseudo.count &&
        1 != e.viewNode.nodeType
      ) {
        let t = e.viewNode.textContent,
          r = t.match(ls),
          s = r ? r[0].length : 0;
        if (
          !r &&
          3 === (null == (i = e.sourceNode) ? void 0 : i.nodeType) &&
          3 ===
            (null == (n = e.sourceNode.nextSibling) ? void 0 : n.nodeType) &&
          t === e.sourceNode.textContent
        ) {
          let i = t + e.sourceNode.nextSibling.textContent,
            n = i.match(ls);
          if (n) {
            let t = n[0];
            (s = t.length),
              (e.sourceNode.textContent = t),
              (e.viewNode.textContent = t),
              (e.sourceNode.nextSibling.textContent = i.substr(s));
          }
        }
        return this.layoutContext.peelOff(e, s);
      }
      return T(e);
    }
    buildViewToNextBlockEdge(e, t) {
      let i = !1,
        n = A("buildViewToNextBlockEdge");
      return (
        n
          .loopWithFrame((n) => {
            var r;
            if (
              e.viewNode &&
              !xn(e) &&
              "http://www.w3.org/1999/xhtml" ===
                (null == (r = e.viewNode.parentElement)
                  ? void 0
                  : r.namespaceURI) &&
              (t.push(e.copy()), t.length > 1e3)
            ) {
              let t =
                1 === e.viewNode.nodeType
                  ? e.viewNode
                  : e.viewNode.parentElement;
              if (
                bo(this.clientLayout.getElementClientRect(t), this.vertical) >=
                2
              )
                return void n.breakLoop();
            }
            this.maybePeelOff(e, 0).then((r) => {
              r !== e && (xn((e = r)) || t.push(e.copy())),
                this.nextInTree(e).then((t) => {
                  (e = t)
                    ? ((i || !this.layoutConstraint.allowLayout(e)) &&
                        ((i = !0), ((e = e.modify()).overflow = !0)),
                      this.isFloatNodeContext(e) &&
                      (Gn(e.floatReference) || "footnote" === e.floatSide)
                        ? this.layoutFloatOrFootnote(e).then((t) => {
                            (e = t),
                              this.pageFloatLayoutContext.isInvalidated() &&
                                (e = null),
                              e ? n.continueLoop() : n.breakLoop();
                          })
                        : e.inline
                        ? n.continueLoop()
                        : n.breakLoop())
                    : n.breakLoop();
                });
            });
          })
          .then(() => {
            n.finish(e);
          }),
        n.result()
      );
    }
    nextInTree(e, t) {
      return id(this.layoutContext.nextInTree(e, t), this);
    }
    buildDeepElementView(e) {
      if (!e.viewNode) return T(e);
      let t = [],
        i = e.sourceNode,
        n = A("buildDeepElementView");
      return (
        n
          .loopWithFrame((n) => {
            e.viewNode && e.inline && !xn(e)
              ? t.push(e.copy())
              : (t.length > 0 && this.postLayoutBlock(e, t), (t = [])),
              this.maybePeelOff(e, 0).then((r) => {
                let s = r;
                if (s !== e) {
                  let r = s;
                  for (; r && r.sourceNode != i; ) r = r.parent;
                  if (null == r) return (e = s), void n.breakLoop();
                  xn(s) || t.push(s.copy());
                }
                this.nextInTree(s).then((t) => {
                  (e = t) && e.sourceNode != i
                    ? this.layoutConstraint.allowLayout(e)
                      ? n.continueLoop()
                      : (((e = e.modify()).overflow = !0),
                        this.stopAtOverflow ? n.breakLoop() : n.continueLoop())
                    : n.breakLoop();
                });
              });
          })
          .then(() => {
            t.length > 0 && this.postLayoutBlock(e, t), n.finish(e);
          }),
        n.result()
      );
    }
    createFloat(e, t, i, n) {
      let r = this.viewDocument.createElement("div");
      return (
        this.vertical
          ? (n >= this.height && (n -= 0.1),
            n < 1 && (n = 0),
            w(r, "height", `${i}px`),
            w(r, "width", `${n}px`))
          : (i >= this.width && (i -= 0.1),
            i < 1 && (i = 0),
            w(r, "width", `${i}px`),
            w(r, "height", `${n}px`)),
        w(r, "float", t),
        w(r, "clear", t),
        this.element.insertBefore(r, e),
        r
      );
    }
    killFloats() {
      let e = this.element.firstChild;
      for (; e; ) {
        let t = e.nextSibling;
        if (1 == e.nodeType) {
          let t = e,
            i = t.style.cssFloat;
          if ("left" != i && "right" != i && "none" !== i) break;
          this.element.removeChild(t);
        }
        e = t;
      }
    }
    createFloats() {
      let e = this.element.firstChild,
        t = this.bands,
        i = this.vertical ? this.getTopEdge() : this.getLeftEdge(),
        n = this.vertical ? this.getBottomEdge() : this.getRightEdge(),
        r = null;
      for (let s of t) {
        let t = s.y2 - s.y1;
        (s.left = this.createFloat(e, "left", s.x1 - i, t)),
          (s.right = this.createFloat(e, "right", n - s.x2, t)),
          s.x1 < n && s.x2 > i ? (r = s) : r || w(s.right, "float", "none");
      }
      if (r) {
        let e = t[t.length - 1],
          i = this.vertical ? -this.getLeftEdge() : this.getBottomEdge();
        r !== e &&
          e.y2 >= i &&
          (this.footnoteEdge = this.vertical ? -r.y2 : r.y2);
      }
    }
    calculateEdge(e, t, i, n) {
      let r;
      if (e && bn(e.viewNode)) return NaN;
      if (
        e &&
        e.after &&
        !e.inline &&
        ((r = Wn(e, this.clientLayout, 0, this.vertical)), !isNaN(r))
      )
        return r;
      let s = n - (e = t[i]).boxOffset;
      for (;;) {
        if (((r = Wn(e, this.clientLayout, s, this.vertical)), !isNaN(r)))
          return r;
        if (s > 0) s--;
        else {
          if (--i < 0) return this.beforeEdge;
          1 != (e = t[i]).viewNode.nodeType &&
            (s = e.viewNode.textContent.length);
        }
      }
    }
    parseComputedLength(e) {
      let t = e.match(/^(-?[0-9]*(\.[0-9]*)?)px$/);
      return t ? this.clientLayout.adjustLengthValue(parseFloat(t[0])) : 0;
    }
    getComputedMargin(e) {
      let t = this.clientLayout.getElementComputedStyle(e),
        i = new so(0, 0, 0, 0);
      return (
        t &&
          ((i.left = this.parseComputedLength(t.marginLeft)),
          (i.top = this.parseComputedLength(t.marginTop)),
          (i.right = this.parseComputedLength(t.marginRight)),
          (i.bottom = this.parseComputedLength(t.marginBottom))),
        i
      );
    }
    getComputedPaddingBorder(e) {
      let t = this.clientLayout.getElementComputedStyle(e),
        i = new so(0, 0, 0, 0);
      return (
        t &&
          ((i.left =
            this.parseComputedLength(t.borderLeftWidth) +
            this.parseComputedLength(t.paddingLeft)),
          (i.top =
            this.parseComputedLength(t.borderTopWidth) +
            this.parseComputedLength(t.paddingTop)),
          (i.right =
            this.parseComputedLength(t.borderRightWidth) +
            this.parseComputedLength(t.paddingRight)),
          (i.bottom =
            this.parseComputedLength(t.borderBottomWidth) +
            this.parseComputedLength(t.paddingBottom))),
        i
      );
    }
    getComputedInsets(e) {
      let t = this.clientLayout.getElementComputedStyle(e),
        i = new so(0, 0, 0, 0);
      if (t) {
        if ("border-box" == t.boxSizing) return this.getComputedMargin(e);
        (i.left =
          this.parseComputedLength(t.marginLeft) +
          this.parseComputedLength(t.borderLeftWidth) +
          this.parseComputedLength(t.paddingLeft)),
          (i.top =
            this.parseComputedLength(t.marginTop) +
            this.parseComputedLength(t.borderTopWidth) +
            this.parseComputedLength(t.paddingTop)),
          (i.right =
            this.parseComputedLength(t.marginRight) +
            this.parseComputedLength(t.borderRightWidth) +
            this.parseComputedLength(t.paddingRight)),
          (i.bottom =
            this.parseComputedLength(t.marginBottom) +
            this.parseComputedLength(t.borderBottomWidth) +
            this.parseComputedLength(t.paddingBottom));
      }
      return i;
    }
    setComputedInsets(e, t) {
      let i = this.clientLayout.getElementComputedStyle(e);
      i &&
        ((t.marginLeft = this.parseComputedLength(i.marginLeft)),
        (t.borderLeft = this.parseComputedLength(i.borderLeftWidth)),
        (t.paddingLeft = this.parseComputedLength(i.paddingLeft)),
        (t.marginTop = this.parseComputedLength(i.marginTop)),
        (t.borderTop = this.parseComputedLength(i.borderTopWidth)),
        (t.paddingTop = this.parseComputedLength(i.paddingTop)),
        (t.marginRight = this.parseComputedLength(i.marginRight)),
        (t.borderRight = this.parseComputedLength(i.borderRightWidth)),
        (t.paddingRight = this.parseComputedLength(i.paddingRight)),
        (t.marginBottom = this.parseComputedLength(i.marginBottom)),
        (t.borderBottom = this.parseComputedLength(i.borderBottomWidth)),
        (t.paddingBottom = this.parseComputedLength(i.paddingBottom)));
    }
    setComputedWidthAndHeight(e, t) {
      let i = this.clientLayout.getElementComputedStyle(e);
      i &&
        ((t.width = this.parseComputedLength(i.width)),
        (t.height = this.parseComputedLength(i.height)));
    }
    layoutUnbreakable(e) {
      return this.buildDeepElementView(e);
    }
    layoutFloat(e) {
      let t = A("layoutFloat"),
        i = e.viewNode,
        n = Hc(
          e.floatSide,
          e.vertical,
          e.direction,
          this.layoutContext.page.side
        );
      return (
        w(i, "float", "none"),
        w(i, "display", "inline-block"),
        w(i, "vertical-align", "top"),
        this.buildDeepElementView(e).then((r) => {
          let s = vt(this.clientLayout, i, this.vertical),
            o = this.getComputedMargin(i),
            a = new He(
              s.left - o.left,
              s.top - o.top,
              s.right + o.right,
              s.bottom + o.bottom
            ),
            l = this.rtl ? this.endEdge : this.startEdge,
            h = this.rtl ? this.startEdge : this.endEdge,
            u = e.parent;
          for (; u && u.inline; ) u = u.parent;
          if (u) {
            let t = u.viewNode.ownerDocument.createElement("div");
            (t.style.left = "0px"),
              (t.style.top = "0px"),
              this.vertical
                ? ((t.style.bottom = "0px"), (t.style.width = "1px"))
                : ((t.style.right = "0px"), (t.style.height = "1px")),
              u.viewNode.appendChild(t);
            let r = vt(this.clientLayout, t, this.vertical);
            (l = Math.max(
              this.rtl ? this.getEndEdge(r) : this.getStartEdge(r),
              l
            )),
              (h = Math.min(
                this.rtl ? this.getStartEdge(r) : this.getEndEdge(r),
                h
              )),
              u.viewNode.removeChild(t);
            let s = this.vertical ? a.y2 - a.y1 : a.x2 - a.x1;
            "left" == n ? (h = Math.max(h, l + s)) : (l = Math.min(l, h - s)),
              u !== e.parent &&
                !e.firstPseudo &&
                (u.viewNode.appendChild(i),
                i.setAttribute("data-vivliostyle-float-box-moved", "true"));
          }
          let c = new He(
              l,
              this.getBoxDir() * this.beforeEdge,
              h,
              this.getBoxDir() * this.afterEdge
            ),
            d = a;
          this.vertical && (d = oo(a));
          let p = this.getBoxDir();
          if (d.y1 < this.bottommostFloatTop * p) {
            let e = d.y2 - d.y1;
            (d.y1 = this.bottommostFloatTop * p), (d.y2 = d.y1 + e);
          }
          ch(c, this.bands, d, n), this.vertical && (a = Lr(d));
          let f = this.getComputedInsets(i);
          w(i, "width", a.x2 - a.x1 - f.left - f.right + "px"),
            w(i, "height", a.y2 - a.y1 - f.top - f.bottom + "px"),
            w(i, "position", "absolute"),
            e.display,
            w(i, "display", e.display);
          let g,
            m = null;
          if (
            (u &&
              (m = u.containingBlockForAbsolute
                ? u
                : u.getContainingBlockForAbsolute()),
            m)
          ) {
            let e = m.viewNode.ownerDocument.createElement("div");
            (e.style.position = "absolute"),
              m.vertical ? (e.style.right = "0") : (e.style.left = "0"),
              (e.style.top = "0"),
              m.viewNode.appendChild(e),
              (g = vt(this.clientLayout, e, this.vertical)),
              m.viewNode.removeChild(e);
          } else
            g = {
              left: this.getLeftEdge() - this.paddingLeft,
              right: this.getRightEdge() + this.paddingRight,
              top: this.getTopEdge() - this.paddingTop,
            };
          (m ? m.vertical : this.vertical)
            ? w(i, "right", g.right - a.x2 + "px")
            : w(i, "left", a.x1 - g.left + "px"),
            w(i, "top", a.y1 - g.top + "px"),
            e.clearSpacer &&
              (e.clearSpacer.parentNode.removeChild(e.clearSpacer),
              (e.clearSpacer = null));
          let b = this.vertical ? a.x1 : a.y2,
            v = this.vertical ? a.x2 : a.y1;
          this.isOverflown(b) && 0 != this.breakPositions.length
            ? (((e = e.modify()).overflow = !0), t.finish(e))
            : (this.killFloats(),
              (c = new He(
                this.getLeftEdge(),
                this.getTopEdge(),
                this.getRightEdge(),
                this.getBottomEdge()
              )),
              this.vertical && (c = oo(c)),
              uh(c, this.bands, d, null, n),
              this.createFloats(),
              "left" == n
                ? (this.leftFloatEdge = b)
                : (this.rightFloatEdge = b),
              (this.bottommostFloatTop = v),
              this.updateMaxReachedAfterEdge(b),
              t.finish(r));
        }),
        t.result()
      );
    }
    setupFloatArea(e, t, i, n, r, s) {
      let o = this.pageFloatLayoutContext,
        a = o.getContainer(t),
        l = e.element;
      a.element.parentNode.appendChild(l),
        (e.isFloat = !0),
        (e.originX = a.originX),
        (e.originY = a.originY),
        (e.vertical = a.vertical),
        (e.rtl = a.rtl),
        (e.marginLeft = e.marginRight = e.marginTop = e.marginBottom = 0),
        (e.borderLeft = e.borderRight = e.borderTop = e.borderBottom = 0),
        (e.paddingLeft = e.paddingRight = e.paddingTop = e.paddingBottom = 0),
        (e.exclusions = (a.exclusions || []).concat()),
        (e.forceNonfitting = !o.hasFloatFragments()),
        (e.innerShape = null);
      let h = a.getPaddingRect();
      e.setHorizontalPosition(h.x1 - a.originX, h.x2 - h.x1),
        e.setVerticalPosition(h.y1 - a.originY, h.y2 - h.y1),
        r.adjustPageFloatArea(e, a, this),
        e.init();
      let u = !!o.setFloatAreaDimensions(
        e,
        t,
        i,
        n,
        !0,
        !o.hasFloatFragments(),
        s
      );
      return (
        u ? (e.killFloats(), e.init()) : a.element.parentNode.removeChild(l), u
      );
    }
    createPageFloatArea(e, t, i, n, r) {
      let s = this.element.ownerDocument.createElement("div");
      w(s, "position", "absolute");
      let o = this.pageFloatLayoutContext.getPageFloatLayoutContext(
          e.floatReference
        ),
        a = new Cn(
          null,
          le.COLUMN,
          null,
          this.pageFloatLayoutContext.flowName,
          e.nodePosition,
          null,
          null
        ),
        l = o.getContainer(),
        h = new od(
          t,
          s,
          this.layoutContext.clone(),
          this.clientLayout,
          this.layoutConstraint,
          a,
          l
        );
      return (
        a.setContainer(h),
        this.setupFloatArea(h, e.floatReference, t, i, n, r) ? h : null
      );
    }
    layoutSinglePageFloatFragment(e, t, i, n, r, s, o) {
      let a = this.pageFloatLayoutContext,
        l = (e = (o ? o.continuations : []).concat(e))[0].float,
        h = a.getPageFloatPlacementCondition(l, t, i),
        u = this.createPageFloatArea(l, t, s, r, h),
        c = { floatArea: u, pageFloatFragment: null, newPosition: null };
      if (!u) return T(c);
      let d = A("layoutSinglePageFloatFragment"),
        p = !1,
        f = 0;
      return (
        d
          .loopWithFrame((t) => {
            if (f >= e.length) return void t.breakLoop();
            let i = e[f],
              r = new ht(i.nodePosition);
            u.layout(r, !0).then((e) => {
              (c.newPosition = e),
                !e || n ? (f++, t.continueLoop()) : ((p = !0), t.breakLoop());
            });
          })
          .then(() => {
            if (!p) {
              let o = a.setFloatAreaDimensions(
                u,
                l.floatReference,
                t,
                s,
                !1,
                n,
                h
              );
              if (o) {
                let t = r.createPageFloatFragment(e, o, i, u, !!c.newPosition);
                a.addPageFloatFragment(t, !0), (c.pageFloatFragment = t);
              } else p = !0;
            }
            d.finish(c);
          }),
        d.result()
      );
    }
    layoutPageFloatInner(e, t, i, n) {
      let r = this.pageFloatLayoutContext,
        s = e.float;
      function o(t, i) {
        i
          ? r.removePageFloatFragment(i, !0)
          : t && t.element.parentNode.removeChild(t.element),
          r.restoreStashedFragments(s.floatReference),
          r.deferPageFloat(e);
      }
      r.stashEndFloatFragments(s);
      let a = A("layoutPageFloatInner");
      return (
        this.layoutSinglePageFloatFragment(
          [e],
          s.floatSide,
          s.clearSide,
          !r.hasFloatFragments(),
          t,
          i,
          n
        ).then((e) => {
          let t = e.floatArea,
            i = e.pageFloatFragment,
            l = e.newPosition;
          i
            ? this.layoutStashedPageFloats(s.floatReference, [n]).then((e) => {
                if (e) {
                  if (
                    (r.addPageFloatFragment(i),
                    r.discardStashedFragments(s.floatReference),
                    l)
                  ) {
                    let e = new ai(s, l.primary);
                    r.deferPageFloat(e);
                  }
                  a.finish(!0);
                } else o(t, i), a.finish(!1);
              })
            : (o(t, i), a.finish(!1));
        }),
        a.result()
      );
    }
    layoutStashedPageFloats(e, t) {
      let i = this.pageFloatLayoutContext,
        n = i.getStashedFloatFragments(e),
        r = [],
        s = [],
        o = !1,
        a = A("layoutStashedPageFloats"),
        l = 0;
      return (
        a
          .loopWithFrame((e) => {
            if (l >= n.length) return void e.breakLoop();
            let i = n[l];
            if (t.includes(i)) return l++, void e.continueLoop();
            let a = new zt().findByFloat(i.continuations[0].float);
            this.layoutSinglePageFloatFragment(
              i.continuations,
              i.floatSide,
              i.clearSide,
              !1,
              a,
              null
            ).then((t) => {
              let i = t.floatArea;
              i && r.push(i);
              let n = t.pageFloatFragment;
              n
                ? (s.push(n), l++, e.continueLoop())
                : ((o = !0), e.breakLoop());
            });
          })
          .then(() => {
            o
              ? (s.forEach((e) => {
                  i.removePageFloatFragment(e, !0);
                }),
                r.forEach((e) => {
                  let t = e.element;
                  t && t.parentNode && t.parentNode.removeChild(t);
                }))
              : n.forEach((e) => {
                  let t = e.area.element;
                  t && t.parentNode && t.parentNode.removeChild(t);
                }),
              a.finish(!o);
          }),
        a.result()
      );
    }
    setFloatAnchorViewNode(e) {
      let t = e.viewNode.parentNode,
        i = t.ownerDocument.createElement("span");
      i.setAttribute(Ht, "1"),
        "footnote" === e.floatSide &&
          this.layoutContext.applyPseudoelementStyle(e, "footnote-call", i),
        t.appendChild(i),
        t.removeChild(e.viewNode);
      let n = e.modify();
      return (n.after = !0), (n.viewNode = i), n;
    }
    resolveFloatReferenceFromColumnSpan(e, t, i) {
      let n = A("resolveFloatReferenceFromColumnSpan"),
        r = this.pageFloatLayoutContext,
        s = r.getPageFloatLayoutContext(le.REGION);
      return (
        r.getContainer().width < s.getContainer().width && e === le.COLUMN
          ? t === b.auto
            ? this.buildDeepElementView(i.copy()).then((t) => {
                let i = t.viewNode,
                  r = po(this.clientLayout, i, ["min-content inline size"])[
                    "min-content inline size"
                  ],
                  s = this.getComputedMargin(i);
                this.vertical
                  ? (r += s.top + s.bottom)
                  : (r += s.left + s.right),
                  r > this.width ? n.finish(le.REGION) : n.finish(e);
              })
            : t === b.all
            ? n.finish(le.REGION)
            : n.finish(e)
          : n.finish(e),
        n.result()
      );
    }
    layoutPageFloat(e) {
      let t,
        i = this.pageFloatLayoutContext,
        n = new zt().findByNodeContext(e),
        r = i.findPageFloatByNodePosition(e.toNodePosition());
      return (
        (t = r ? T(r) : n.createPageFloat(e, i, this)),
        t.thenAsync((t) => {
          let r = vs(e, 0),
            s = this.setFloatAnchorViewNode(e),
            o = n.findPageFloatFragment(t, i),
            a = new ai(t, r);
          if (o && o.hasFloat(t))
            return i.registerPageFloatAnchor(t, s.viewNode), T(s);
          if (i.isForbidden(t) || i.hasPrecedingFloatsDeferredToNext(t))
            return (
              i.deferPageFloat(a),
              i.registerPageFloatAnchor(t, s.viewNode),
              T(s)
            );
          if (this.nodeContextOverflowingDueToRepetitiveElements)
            return T(null);
          {
            let e = Wn(s, this.clientLayout, 0, this.vertical);
            return this.isOverflown(e)
              ? T(s)
              : this.layoutPageFloatInner(a, n, e, o).thenAsync((e) =>
                  e ? T(null) : (i.registerPageFloatAnchor(t, s.viewNode), T(s))
                );
          }
        })
      );
    }
    processLineStyling(e, t, i) {
      let n = A("processLineStyling"),
        r = i.concat([]);
      i.splice(0, i.length);
      let s = 0,
        o = e.firstPseudo;
      return (
        0 == o.count && (o = o.outer),
        n
          .loopWithFrame((i) => {
            if (!o) return void i.breakLoop();
            let n = this.findLinePositions(r),
              a = o.count - s;
            if (n.length <= a) return void i.breakLoop();
            let l = this.findAcceptableBreakInside(r, n[a - 1], !0);
            null != l
              ? this.finishBreak(l, !1, !1).then(() => {
                  (s += a),
                    this.layoutContext.peelOff(l, 0).then((n) => {
                      (o = (e = n).firstPseudo),
                        (r = []),
                        this.buildViewToNextBlockEdge(e, r).then((e) => {
                          (t = e), i.continueLoop();
                        });
                    });
                })
              : i.breakLoop();
          })
          .then(() => {
            Array.prototype.push.apply(i, r), n.finish(t);
          }),
        n.result()
      );
    }
    isLoneImage(e) {
      return (
        !(2 != e.length && this.breakPositions.length > 0) &&
        e[0].sourceNode == e[1].sourceNode &&
        Zt[e[0].sourceNode.localName]
      );
    }
    getTrailingMarginEdgeAdjustment(e) {
      let t = 0,
        i = 0;
      for (let n = e.length - 1; n >= 0; n--) {
        let r = e[n];
        if (!r.after || !r.viewNode || 1 != r.viewNode.nodeType) break;
        let s = this.getComputedMargin(r.viewNode),
          o = this.vertical ? -s.left : s.bottom;
        o > 0 ? (t = Math.max(t, o)) : (i = Math.min(i, o));
      }
      return t + i;
    }
    layoutBreakableBlock(e) {
      let t = A("layoutBreakableBlock"),
        i = [];
      return (
        this.buildViewToNextBlockEdge(e, i).then((n) => {
          let r = i.length - 1;
          if (r < 0) return void t.finish(n);
          this.postLayoutBlock(n, i);
          let s,
            o = this.calculateEdge(n, i, r, i[r].boxOffset),
            a = !1;
          if (!n || !bn(n.viewNode)) {
            let e = Xn(n, this.collectElementsOffset());
            (a = this.isOverflown(o + (this.vertical ? -1 : 1) * e.minimum)),
              this.isOverflown(o + (this.vertical ? -1 : 1) * e.current) &&
                !this.nodeContextOverflowingDueToRepetitiveElements &&
                (this.nodeContextOverflowingDueToRepetitiveElements = n);
          }
          null == n && (o += this.getTrailingMarginEdgeAdjustment(i)),
            this.updateMaxReachedAfterEdge(o),
            (s = e.firstPseudo ? this.processLineStyling(e, n, i) : T(n)),
            s.then((e) => {
              i.length > 0 &&
                (this.saveBoxBreakPosition(i),
                a &&
                  !this.isLoneImage(i) &&
                  e &&
                  ((e = e.modify()).overflow = !0)),
                t.finish(e);
            });
        }),
        t.result()
      );
    }
    postLayoutBlock(e, t) {
      Ge("POST_LAYOUT_BLOCK").forEach((i) => {
        i(e, t, this);
      });
    }
    findEndOfLine(e, t, i) {
      let n,
        r = this.vertical ? e - 1 : e + 1,
        s = 0,
        o = t[0].boxOffset,
        a = s,
        l = t.length - 1,
        h = t[l].boxOffset;
      for (; o < h; ) {
        (n = o + Math.ceil((h - o) / 2)), (a = s);
        let e = l;
        for (; a < e; ) {
          let i = a + Math.ceil((e - a) / 2);
          t[i].boxOffset > n ? (e = i - 1) : (a = i);
        }
        let u = this.calculateEdge(null, t, a, n);
        if (this.vertical ? u <= r : u >= r) {
          for (h = n - 1; t[a].boxOffset == n; ) a--;
          l = a;
        } else i && this.updateMaxReachedAfterEdge(u), (o = n), (s = a);
      }
      return { nodeContext: t[a], index: o, checkPointIndex: a };
    }
    findAcceptableBreakInside(e, t, i) {
      var n, r, s;
      let o = this.findEndOfLine(t, e, !0),
        a = o.nodeContext;
      if (0 === o.checkPointIndex && o.index === a.boxOffset) return null;
      let l = a.viewNode;
      if (
        1 != l.nodeType &&
        "viv-ts-inner" !==
          (null == (n = l.parentElement) ? void 0 : n.localName)
      ) {
        let t = l;
        a = this.resolveTextNodeBreaker(a).breakTextNode(
          t,
          a,
          o.index,
          e,
          o.checkPointIndex,
          i
        );
      } else {
        let e = Kc(a);
        if (e) {
          if (
            (null == (r = this.breakPositions) ? void 0 : r[0]) instanceof Bs &&
            null != e &&
            e.viewNode.contains(this.breakPositions[0].checkPoints[0].viewNode)
          )
            return null;
          for (a = e; !a.after && a.inline && a.parent; ) {
            let e = null == (s = a.viewNode) ? void 0 : s.previousSibling;
            for (; e && (de(e, a.parent.whitespace) || Ts(e)); )
              e = e.previousSibling;
            if (e) break;
            a = a.parent;
          }
        }
      }
      return this.clearOverflownViewNodes(a, !1), a;
    }
    resolveTextNodeBreaker(e) {
      return Ge("RESOLVE_TEXT_NODE_BREAKER").reduce(
        (t, i) => i(e) || t,
        Ao.instance
      );
    }
    getRangeBoxes(e, t) {
      let i = [],
        n = e.ownerDocument.createRange(),
        r = !1,
        s = e,
        o = null,
        a = !1,
        l = !0;
      for (; l; ) {
        let e = !0;
        do {
          let i = null;
          s == t && (l = 1 === t.nodeType && !(!t.firstChild || r));
          let h = 1 === s.nodeType ? s : null;
          h
            ? r
              ? (r = !1)
              : aa(h)
              ? (e = !a)
              : !h.firstChild ||
                Zt[h.localName] ||
                /^r(uby|[bt]c?)$/.test(h.localName) ||
                rf(this.clientLayout.getElementComputedStyle(h).display)
              ? ((e = !a),
                e
                  ? ("ruby" === h.localName &&
                      s.firstChild &&
                      (s = s.firstChild),
                    n.setStartBefore(s),
                    (a = !0),
                    (o = s))
                  : /^r(uby|tc?)$/.test(h.localName) || (o = s),
                s.contains(t) && (l = !1))
              : (i = s.firstChild)
            : (a ||
                (null == s.parentNode
                  ? (l = !1)
                  : (n.setStartBefore(s), (a = !0))),
              (o = s)),
            i || ((i = s.nextSibling), i || ((r = !0), (i = s.parentNode))),
            (s = i);
        } while (e && l);
        if (a) {
          n.setEndAfter(o);
          let e = this.clientLayout.getRangeClientRects(n);
          Yc(e, this.vertical);
          for (let t = 0; t < e.length; t++) i.push(e[t]);
          a = !1;
        }
      }
      return i;
    }
    findLinePositions(e) {
      let t = [],
        i = this.getRangeBoxes(e[0].viewNode, e[e.length - 1].viewNode);
      i.sort(this.vertical ? jh : Xh);
      let n = 0,
        r = 0,
        s = 0,
        o = 0,
        a = 0,
        l = this.getBoxDir();
      for (;;) {
        if (a < i.length) {
          let e = i[a],
            t = 1;
          if (o > 0) {
            let i = Math.max(this.getBoxSize(e), 1);
            t =
              l * this.getBeforeEdge(e) < l * n
                ? (l * (this.getAfterEdge(e) - n)) / i
                : l * this.getAfterEdge(e) > l * r
                ? (l * (r - this.getBeforeEdge(e))) / i
                : 1;
          }
          if (
            0 == o ||
            t >= 0.6 ||
            (t >= 0.2 && this.getStartEdge(e) >= s - 1)
          ) {
            (s = this.getEndEdge(e)),
              this.vertical
                ? ((n = 0 == o ? e.right : Math.max(n, e.right)),
                  (r = 0 == o ? e.left : Math.min(r, e.left)))
                : ((n = 0 == o ? e.top : Math.min(n, e.top)),
                  (r = 0 == o ? e.bottom : Math.max(r, e.bottom))),
              o++,
              a++;
            continue;
          }
        }
        if ((o > 0 && (t.push(r), (o = 0)), a >= i.length)) break;
      }
      return t.sort(rr), this.vertical && t.reverse(), t;
    }
    calculateClonedPaddingBorder(e) {
      let t = 0;
      for (let i = e; i; i = i.parent)
        if (!i.inline && Es(i.viewNode)) {
          let e = this.getComputedPaddingBorder(i.viewNode);
          (t += i.vertical ? -e.left : e.bottom),
            "table" === i.display &&
              (t += (i.vertical ? -1 : 1) * i.blockBorderSpacing);
        }
      return t;
    }
    getOffsetByRepetitiveElements(e) {
      let t;
      return (
        (t = e
          ? e.calculateOffset(this)
          : Xn(null, this.collectElementsOffset())),
        t.current
      );
    }
    findBoxBreakPosition(e, t) {
      let i = this.element.parentNode,
        n = this.element.nextSibling;
      i.removeChild(this.element), i.insertBefore(this.element, n);
      let r,
        s,
        o = e.checkPoints,
        a = o[0];
      for (; a.parent && a.inline; ) a = a.parent;
      t
        ? ((r = 1), (s = 1))
        : ((r = Math.max((a.inheritedProps.widows || 2) - 0, 1)),
          (s = Math.max((a.inheritedProps.orphans || 2) - 0, 1)));
      let l = this.calculateClonedPaddingBorder(a),
        h = this.findLinePositions(o),
        u = this.footnoteEdge - l,
        c = this.getBoxDir(),
        d = this.getOffsetByRepetitiveElements(e);
      u -= c * d;
      let p = this.findFirstOverflowingEdgeAndCheckPoint(o);
      isNaN(p.edge) && (p.edge = c * (1 / 0));
      let f = gt(h.length, (e) => {
          let t = h[e];
          return this.vertical ? t < u || t <= p.edge : t > u || t >= p.edge;
        }),
        g = f <= 0;
      g && (f = gt(h.length, (e) => (this.vertical ? h[e] < u : h[e] > u)));
      let m,
        w = o[o.length - 1].viewNode;
      if (
        ("viv-ts-inner" === (null == w ? void 0 : w.parentElement.localName) &&
          (w = w.parentElement.parentElement),
        ((f === h.length && w.nextSibling) ||
          (f >= h.length - 1 &&
            w.parentElement.querySelector(".MJXc-display"))) &&
          (r = 0),
        (f = Math.min(h.length - r, f)),
        f < s)
      )
        return null;
      if (
        ((u = h[f - 1]),
        (m = g
          ? p.checkPoint
          : this.findAcceptableBreakInside(e.checkPoints, u, t)),
        m)
      ) {
        let e = this.getAfterEdgeOfBlockContainer(m);
        !isNaN(e) && c * (u - e) > 0 && (u = e),
          (this.computedBlockSize = c * (u - this.beforeEdge) + d);
      }
      return m;
    }
    getAfterEdgeOfBlockContainer(e) {
      let t = e;
      do {
        t = t.parent;
      } while (t && t.inline);
      return t
        ? ((t = t.copy().modify()),
          (t.after = !0),
          Wn(t, this.clientLayout, 0, this.vertical))
        : NaN;
    }
    findFirstOverflowingEdgeAndCheckPoint(e) {
      let t = e.findIndex((e) => e.overflow);
      if (t < 0) return { edge: NaN, checkPoint: null };
      let i = e[t];
      return {
        edge: this.calculateEdge(null, e, t, i.boxOffset),
        checkPoint: i,
      };
    }
    findEdgeBreakPosition(e) {
      return (
        (this.computedBlockSize =
          e.computedBlockSize + this.getOffsetByRepetitiveElements(e)),
        e.position
      );
    }
    finishBreak(e, t, i) {
      e.formattingContext;
      let n = new jn().find(e.formattingContext).finishBreak(this, e, t, i);
      return n || (n = hi.finishBreak(this, e, t, i)), n;
    }
    findAcceptableBreakPosition() {
      let e = null,
        t = null,
        i = 0,
        n = 0;
      do {
        (i = n), (n = Number.MAX_VALUE);
        for (let r = this.breakPositions.length - 1; r >= 0 && !t; --r) {
          (e = this.breakPositions[r]), (t = e.findAcceptableBreak(this, i));
          let s = e.getMinBreakPenalty();
          s > i && (n = Math.min(n, s));
        }
      } while (n > i && !t && this.forceNonfitting);
      return { breakPosition: t ? e : null, nodeContext: t };
    }
    doFinishBreak(e, t, i, n) {
      if (
        this.pageFloatLayoutContext.isInvalidated() ||
        this.pageBreakType ||
        !t
      )
        return T(e);
      let r = A("doFinishBreak"),
        s = !1;
      if (!e) {
        if (this.forceNonfitting)
          return (
            V.warn("Could not find any page breaks?!!"),
            this.skipTailEdges(t).then((e) => {
              e
                ? (((e = e.modify()).overflow = !1),
                  this.finishBreak(e, s, !0).then(() => {
                    r.finish(e);
                  }))
                : r.finish(e);
            }),
            r.result()
          );
        (e = i), (s = !0), (this.computedBlockSize = n);
      }
      return (
        this.finishBreak(e, s, !0).then(() => {
          r.finish(e);
        }),
        r.result()
      );
    }
    isBreakable(e) {
      for (let t = e; t; t = t.parent) if (Ts(t.viewNode)) return !1;
      return (
        !!e.after ||
        ("http://www.w3.org/2000/svg" !== e.sourceNode.namespaceURI &&
          !e.flexContainer)
      );
    }
    zeroIndent(e) {
      let t = e.toString();
      return "" == t || "auto" == t || !!t.match(/^0+(.0*)?[^0-9]/);
    }
    checkOverflowAndSaveEdge(e, t) {
      if (!e) return !1;
      for (let t = e; t; t = t.parent) if (Ts(t.viewNode)) return !1;
      if (bn(e.viewNode)) return !1;
      let i = Wn(e, this.clientLayout, 0, this.vertical),
        n = Xn(e, this.collectElementsOffset()),
        r = this.isOverflown(i + (this.vertical ? -1 : 1) * n.minimum);
      if (
        this.isOverflown(i + (this.vertical ? -1 : 1) * n.current) &&
        !this.nodeContextOverflowingDueToRepetitiveElements
      )
        this.nodeContextOverflowingDueToRepetitiveElements = e;
      else if (t) {
        let e = i + this.getTrailingMarginEdgeAdjustment(t),
          r = this.footnoteEdge - this.getBoxDir() * n.current;
        i = this.vertical
          ? Math.min(i, Math.max(e, r))
          : Math.max(i, Math.min(e, r));
      }
      return this.updateMaxReachedAfterEdge(i), r;
    }
    checkOverflowAndSaveEdgeAndBreakPosition(e, t, i, n) {
      if (!e || bn(e.viewNode)) return !1;
      let r = this.checkOverflowAndSaveEdge(e, t);
      return (i || !r) && this.saveEdgeBreakPosition(e, n, r), r;
    }
    applyClearance(e) {
      if (!e.viewNode.parentNode || e.floatReference !== le.INLINE) return !1;
      let t = this.getComputedMargin(e.viewNode),
        i = e.viewNode.ownerDocument.createElement("div");
      this.vertical
        ? ((i.style.bottom = "0px"),
          (i.style.width = "1px"),
          (i.style.marginRight = `${t.right}px`))
        : ((i.style.right = "0px"),
          (i.style.height = "1px"),
          (i.style.marginTop = `${t.top}px`)),
        e.viewNode.parentNode.insertBefore(i, e.viewNode);
      let n = vt(this.clientLayout, i, this.vertical),
        r = this.getBeforeEdge(n),
        s = this.getBoxDir(),
        o = "same" === e.clearSide ? e.floatSide : e.clearSide,
        a = /^(top|bottom|inside|outside|(block|inline)-(start|end))$/.test(o)
          ? Hc(o, e.vertical, e.direction, this.layoutContext.page.side)
          : o,
        l =
          "left" === a || "right" === a
            ? ("rtl" === e.direction) == ("left" === a)
              ? "inline-end"
              : "inline-start"
            : a,
        h = this.pageFloatLayoutContext.getPageFloatClearEdge(l, this);
      switch (a) {
        case "left":
          h = s * Math.max(h * s, this.leftFloatEdge * s);
          break;
        case "right":
          h = s * Math.max(h * s, this.rightFloatEdge * s);
          break;
        default:
          h =
            s *
            Math.max(
              h * s,
              Math.max(this.rightFloatEdge * s, this.leftFloatEdge * s)
            );
      }
      if (r * s >= h * s) return e.viewNode.parentNode.removeChild(i), !1;
      {
        let o = Math.max(1, (h - r) * s);
        this.vertical
          ? (i.style.width = `${o}px`)
          : (i.style.height = `${o}px`),
          (n = vt(this.clientLayout, i, this.vertical));
        let a = this.getAfterEdge(n);
        if (!e.floatSide)
          if (this.vertical) {
            let e = a - t.right - h;
            e > 0 == t.right >= 0 && (e += t.right),
              (i.style.marginLeft = `${e}px`);
          } else {
            let e = h - (a + t.top);
            e > 0 == t.top >= 0 && (e += t.top),
              (i.style.marginBottom = `${e}px`);
          }
        return (e.clearSpacer = i), !0;
      }
    }
    isBFC(e) {
      return !(
        !af(e) && !on.isInstanceOfRepetitiveElementsOwnerFormattingContext(e)
      );
    }
    skipEdges(e, t, i) {
      var n;
      let r = e.after
        ? null == (n = e.parent)
          ? void 0
          : n.formattingContext
        : e.formattingContext;
      if (r && !this.isBFC(r)) return T(e);
      let s = A("skipEdges"),
        o = !i && t && e && e.after,
        a = i,
        l = null,
        h = [],
        u = [],
        c = !1;
      function d() {
        return (
          !!i ||
          (!t &&
            Ie(a) &&
            !(function () {
              var t;
              if (!l || e.floatSide) return !1;
              for (let e = l; null != e && e.parent; e = e.parent) {
                let i = e.after
                  ? e.viewNode
                  : null == (t = e.viewNode)
                  ? void 0
                  : t.previousSibling;
                for (; i && (de(i, e.parent.whitespace) || Ts(i)); )
                  i = i.previousSibling;
                if (i) return !1;
              }
              return !0;
            })())
        );
      }
      let p = () => {
        (e = h[0] || e).viewNode.parentNode.removeChild(e.viewNode),
          (this.pageBreakType = a);
      };
      return (
        s
          .loopWithFrame((i) => {
            for (var n; e; ) {
              e.formattingContext;
              let r = new jn().find(e.formattingContext);
              do {
                if (!e.viewNode) break;
                if (e.inline && 1 != e.viewNode.nodeType) {
                  if (de(e.viewNode, e.whitespace)) break;
                  if (!e.after)
                    return (
                      d()
                        ? p()
                        : this.checkOverflowAndSaveEdgeAndBreakPosition(
                            l,
                            null,
                            !0,
                            a
                          )
                        ? ((e = (
                            (this.stopAtOverflow && l) ||
                            e
                          ).modify()).overflow = !0)
                        : ((e = e.modify()).breakBefore = a),
                      void i.breakLoop()
                    );
                }
                if (!e.after) {
                  if (
                    (e.floatSide &&
                      (this.breakAtTheEdgeBeforeFloat = ho(a) ? a : null),
                    r && r.startNonInlineElementNode(e))
                  )
                    break;
                  if (
                    (e.clearSide &&
                      this.applyClearance(e) &&
                      t &&
                      0 === this.breakPositions.length &&
                      this.saveEdgeBreakPosition(e.copy(), a, !1),
                    !e.inline &&
                      !e.repeatOnBreak &&
                      (l
                        ? Kc(l)
                        : this.breakPositions[
                            this.breakPositions.length - 1
                          ] instanceof Bs) &&
                      this.saveEdgeBreakPosition(e.copy(), a, !1),
                    !this.isBFC(e.formattingContext) ||
                      on.isInstanceOfRepetitiveElementsOwnerFormattingContext(
                        e.formattingContext
                      ) ||
                      this.isFloatNodeContext(e) ||
                      e.flexContainer ||
                      (!e.nodeShadow &&
                        !e.sourceNode.firstElementChild &&
                        de(e.sourceNode.firstChild, e.whitespace)))
                  )
                    return (
                      h.push(e.copy()),
                      (a = Ve(a, e.breakBefore)),
                      d()
                        ? p()
                        : (this.checkOverflowAndSaveEdgeAndBreakPosition(
                            l,
                            null,
                            !0,
                            a
                          ) ||
                            !this.layoutConstraint.allowLayout(e)) &&
                          ((e = (
                            (this.stopAtOverflow && l) ||
                            e
                          ).modify()).overflow = !0),
                      void i.breakLoop()
                    );
                }
                if (1 != e.viewNode.nodeType) break;
                let s = e.viewNode,
                  f = s.style;
                if (e.after) {
                  "balance" === f.columnFill &&
                    "auto" === s.getAttribute("data-vivliostyle-column-fill") &&
                    (f.columnFill = "auto"),
                    e.floatSide &&
                      ((a = null != a ? a : this.breakAtTheEdgeBeforeFloat),
                      (this.breakAtTheEdgeBeforeFloat = null));
                  let n = e.sourceNode;
                  if (
                    "svg" === n.localName ||
                    "math" === n.localName ||
                    "true" === n.getAttribute("data-math-typeset")
                  ) {
                    (c = !1),
                      (l = e.copy()),
                      u.push(l),
                      (a = Ve(null, e.breakAfter)),
                      this.checkOverflowAndSaveEdgeAndBreakPosition(
                        l,
                        null,
                        !this.stopAtOverflow,
                        a
                      );
                    break;
                  }
                  if (
                    e.inline ||
                    (r && r.afterNonInlineElementNode(e, this.stopAtOverflow))
                  )
                    break;
                  if (c) {
                    if (d()) return p(), void i.breakLoop();
                    (h = []), (t = !1), (o = !1), (a = null);
                  }
                  (c = !1),
                    (l = e.copy()),
                    u.push(l),
                    (a = Ve(a, e.breakAfter)),
                    f &&
                      (!this.zeroIndent(f.paddingBottom) ||
                        !this.zeroIndent(f.borderBottomWidth)) &&
                      (u = [l]);
                } else {
                  if (
                    (h.push(e.copy()),
                    (a = Ve(a, e.breakBefore)),
                    l && Ie(a) && sf(l.viewNode, e.viewNode),
                    (e.pageType !=
                      (null == (n = e.parent) ? void 0 : n.pageType) ||
                      !Ie(a)) &&
                      !this.layoutConstraint.allowLayout(e) &&
                      (this.checkOverflowAndSaveEdgeAndBreakPosition(
                        l,
                        null,
                        !this.stopAtOverflow,
                        a
                      ),
                      ((e = e.modify()).overflow = !0),
                      this.stopAtOverflow))
                  )
                    return void i.breakLoop();
                  let t = e.viewNode.localName;
                  if (Zt[t])
                    return (
                      d()
                        ? p()
                        : this.checkOverflowAndSaveEdgeAndBreakPosition(
                            l,
                            null,
                            !0,
                            a
                          ) &&
                          ((e = (
                            (this.stopAtOverflow && l) ||
                            e
                          ).modify()).overflow = !0),
                      void i.breakLoop()
                    );
                  f &&
                    !(
                      this.zeroIndent(f.paddingTop) &&
                      this.zeroIndent(f.borderTopWidth)
                    ) &&
                    ((o = !1), (u = [])),
                    (c = !0);
                }
              } while (0);
              let s = this.nextInTree(e, o);
              if (s.isPending())
                return void s.then((t) => {
                  (e = t), i.continueLoop();
                });
              e = s.get();
            }
            this.checkOverflowAndSaveEdgeAndBreakPosition(
              l,
              u,
              !this.stopAtOverflow,
              a
            )
              ? l && this.stopAtOverflow && ((e = l.modify()).overflow = !0)
              : Ie(a) && (this.pageBreakType = a),
              i.breakLoop();
          })
          .then(() => {
            l && (this.lastAfterPosition = l.toNodePosition()), s.finish(e);
          }),
        s.result()
      );
    }
    skipTailEdges(e) {
      let t = e.copy(),
        i = A("skipEdges"),
        n = null,
        r = !1;
      return (
        i
          .loopWithFrame((i) => {
            for (; e; ) {
              do {
                if (!e.viewNode) break;
                if (e.inline && 1 != e.viewNode.nodeType) {
                  if (de(e.viewNode, e.whitespace)) break;
                  if (!e.after)
                    return (
                      Ie(n) && (this.pageBreakType = n), void i.breakLoop()
                    );
                }
                if (!e.after && (this.isFloatNodeContext(e) || e.flexContainer))
                  return (
                    (n = Ve(n, e.breakBefore)),
                    Ie(n) && (this.pageBreakType = n),
                    void i.breakLoop()
                  );
                if (1 != e.viewNode.nodeType) break;
                let t = e.viewNode.style;
                if (e.after) {
                  if (r) {
                    if (Ie(n))
                      return (this.pageBreakType = n), void i.breakLoop();
                    n = null;
                  }
                  (r = !1), (n = Ve(n, e.breakAfter));
                } else {
                  n = Ve(n, e.breakBefore);
                  let r = e.viewNode.localName;
                  if (Zt[r])
                    return (
                      Ie(n) && (this.pageBreakType = n), void i.breakLoop()
                    );
                  if (
                    t &&
                    (!this.zeroIndent(t.paddingTop) ||
                      !this.zeroIndent(t.borderTopWidth))
                  )
                    return void i.breakLoop();
                }
                r = !0;
              } while (0);
              let t = this.layoutContext.nextInTree(e);
              if (t.isPending())
                return void t.then((t) => {
                  (e = t), i.continueLoop();
                });
              e = t.get();
            }
            (t = null), i.breakLoop();
          })
          .then(() => {
            i.finish(t);
          }),
        i.result()
      );
    }
    layoutFloatOrFootnote(e) {
      return Gn(e.floatReference) || "footnote" === e.floatSide
        ? this.layoutPageFloat(e)
        : this.layoutFloat(e);
    }
    layoutNext(e, t, i) {
      let n = A("layoutNext");
      return (
        this.skipEdges(e, t, i || null).then((i) => {
          if (!(e = i) || this.pageBreakType || this.stopByOverflow(e))
            n.finish(e);
          else {
            let i = e.formattingContext;
            new jn().find(i).layout(e, this, t).thenFinish(n);
          }
        }),
        n.result()
      );
    }
    clearOverflownViewNodes(e, t) {
      if (e)
        for (let i = e.parent; e; e = i, i = i ? i.parent : null) {
          let n = (i || e).formattingContext;
          new jn().find(n).clearOverflownViewNodes(this, i, e, t), (t = !1);
        }
    }
    initGeom() {
      let e = this.element.ownerDocument.createElement("div");
      (e.style.position = "absolute"),
        (e.style.top = `${this.paddingTop}px`),
        (e.style.right = `${this.paddingRight}px`),
        (e.style.bottom = `${this.paddingBottom}px`),
        (e.style.left = `${this.paddingLeft}px`),
        this.element.appendChild(e);
      let t = this.clientLayout.getElementClientRect(e);
      this.element.removeChild(e);
      let i = this.originX + this.left + this.getInsetLeft(),
        n = this.originY + this.top + this.getInsetTop();
      (this.box = new He(i, n, i + this.width, n + this.height)),
        (this.startEdge = t
          ? this.vertical
            ? this.rtl
              ? t.bottom
              : t.top
            : this.rtl
            ? t.right
            : t.left
          : 0),
        (this.endEdge = t
          ? this.vertical
            ? this.rtl
              ? t.top
              : t.bottom
            : this.rtl
            ? t.left
            : t.right
          : 0),
        (this.beforeEdge = t ? (this.vertical ? t.right : t.top) : 0),
        (this.afterEdge = t ? (this.vertical ? t.left : t.bottom) : 0),
        (this.leftFloatEdge = this.beforeEdge),
        (this.rightFloatEdge = this.beforeEdge),
        (this.bottommostFloatTop = this.beforeEdge),
        (this.footnoteEdge = this.afterEdge),
        (this.bands = oh(
          this.box,
          [this.getInnerShape()],
          this.getExclusions(),
          8,
          this.snapHeight,
          this.vertical
        )),
        this.createFloats();
    }
    init() {
      (this.chunkPositions = []),
        w(this.element, "width", `${this.width}px`),
        w(this.element, "height", `${this.height}px`),
        this.initGeom(),
        (this.computedBlockSize = 0),
        (this.overflown = !1),
        (this.pageBreakType = null),
        (this.lastAfterPosition = null);
    }
    saveEdgeBreakPosition(e, t, i) {
      e.formattingContext;
      let n = e.copy(),
        r = new jn().find(e.formattingContext),
        s = this.calculateClonedPaddingBorder(n),
        o = r.createEdgeBreakPosition(n, t, i, this.computedBlockSize + s);
      this.breakPositions.push(o);
    }
    saveBoxBreakPosition(e) {
      let t = e[0].breakPenalty;
      if (t) {
        let i = e[0];
        for (; i.parent && i.inline; ) i = i.parent;
        t = i.breakPenalty;
      }
      let i = new Bs(e, t);
      this.breakPositions.push(i);
    }
    updateMaxReachedAfterEdge(e) {
      if (!isNaN(e)) {
        let t = this.getBoxDir() * (e - this.beforeEdge);
        this.computedBlockSize = Math.max(t, this.computedBlockSize);
      }
    }
    layout(e, t, i) {
      if (
        (this.chunkPositions.push(e),
        e.primary.after && (this.lastAfterPosition = e.primary),
        this.stopAtOverflow && this.overflown)
      )
        return T(e);
      if (this.isFullWithPageFloats())
        return e.primary.after && 1 === e.primary.steps.length ? T(null) : T(e);
      this.element.hasAttribute("data-vivliostyle-column") && di(this);
      let n = A("layout");
      return (
        this.openAllViews(e.primary).then((e) => {
          let r = null;
          if (e.viewNode) r = e.copy();
          else {
            let e = (t) => {
              t.nodeContext.viewNode &&
                ((r = t.nodeContext),
                this.layoutContext.removeEventListener("nextInTree", e));
            };
            this.layoutContext.addEventListener("nextInTree", e);
          }
          let s = new nd(t, i);
          s.layout(e, this).then((e) => {
            this.doFinishBreak(
              e,
              s.context.overflownNodeContext,
              r,
              s.initialComputedBlockSize
            ).then((e) => {
              let t = null;
              (t = this.pseudoParent
                ? T(null)
                : this.doFinishBreakOfFragmentLayoutConstraints(e)),
                t.then(() => {
                  if (this.pageFloatLayoutContext.isInvalidated())
                    n.finish(null);
                  else if (e) {
                    this.overflown = !0;
                    let t = new ht(e.toNodePosition());
                    n.finish(t);
                  } else n.finish(null);
                });
            });
          });
        }),
        n.result()
      );
    }
    isFullWithPageFloats() {
      return this.pageFloatLayoutContext.isColumnFullWithPageFloats(this);
    }
    getMaxBlockSizeOfPageFloats() {
      return this.pageFloatLayoutContext.getMaxBlockSizeOfPageFloats();
    }
    doFinishBreakOfFragmentLayoutConstraints(e) {
      let t = A("doFinishBreakOfFragmentLayoutConstraints"),
        i = [].concat(this.fragmentLayoutConstraints);
      i.sort(
        (e, t) => e.getPriorityOfFinishBreak() - t.getPriorityOfFinishBreak()
      );
      let n = 0;
      return (
        t
          .loop(() =>
            n < i.length ? i[n++].finishBreak(e, this).thenReturn(!0) : T(!1)
          )
          .then(() => {
            t.finish(!0);
          }),
        t.result()
      );
    }
    doLayout(e, t, i) {
      let n = A("doLayout"),
        r = null;
      return (
        (this.breakPositions = []),
        (this.nodeContextOverflowingDueToRepetitiveElements = null),
        n
          .loopWithFrame((n) => {
            for (; e; ) {
              let s = !0;
              if (
                (this.layoutNext(e, t, i || null).then((o) => {
                  if (
                    ((t = !1),
                    (i = null),
                    this.nodeContextOverflowingDueToRepetitiveElements &&
                    this.stopAtOverflow
                      ? ((this.pageBreakType = null),
                        ((e =
                          this
                            .nodeContextOverflowingDueToRepetitiveElements).overflow =
                          !0))
                      : (e = o),
                    this.pageFloatLayoutContext.isInvalidated())
                  )
                    n.breakLoop();
                  else if (this.pageBreakType) n.breakLoop();
                  else if (e && this.stopByOverflow(e)) {
                    r = e;
                    let t = this.findAcceptableBreakPosition();
                    (e = t.nodeContext),
                      t.breakPosition &&
                        t.breakPosition.breakPositionChosen(this),
                      n.breakLoop();
                  } else s ? (s = !1) : n.continueLoop();
                }),
                s)
              )
                return void (s = !1);
            }
            (this.computedBlockSize += this.getOffsetByRepetitiveElements()),
              n.breakLoop();
          })
          .then(() => {
            n.finish({ nodeContext: e, overflownNodeContext: r });
          }),
        n.result()
      );
    }
    redoLayout() {
      let e = this.chunkPositions,
        t = this.element.lastChild;
      for (; t != this.last; ) {
        let e = t.previousSibling;
        (this.element === t.parentNode &&
          this.layoutContext.isPseudoelement(t)) ||
          this.element.removeChild(t),
          (t = e);
      }
      this.killFloats(), this.init();
      let i = A("redoLayout"),
        n = 0,
        r = null,
        s = !0;
      return (
        i
          .loopWithFrame((t) => {
            if (n < e.length) {
              let i = e[n++];
              this.layout(i, s).then((e) => {
                (s = !1), e ? ((r = e), t.breakLoop()) : t.continueLoop();
              });
            } else t.breakLoop();
          })
          .then(() => {
            i.finish(r);
          }),
        i.result()
      );
    }
    saveDistanceToBlockEndFloats() {
      let e = this.pageFloatLayoutContext.getBlockStartEdgeOfBlockEndFloats();
      e > 0 &&
        isFinite(e) &&
        (this.blockDistanceToBlockEndFloats =
          this.getBoxDir() * (e - this.beforeEdge - this.computedBlockSize));
    }
    collectElementsOffset() {
      let e = [];
      for (let t = this; t; t = t.pseudoParent)
        t.fragmentLayoutConstraints.forEach((t) => {
          if (on.isInstanceOfRepetitiveElementsOwnerLayoutConstraint(t)) {
            let i = t.getRepetitiveElements();
            e.push(i);
          }
          if (si.isInstanceOfAfterIfContinuesLayoutConstraint(t)) {
            let i = t.getRepetitiveElements();
            e.push(i);
          }
          _t.isInstanceOfTableRowLayoutConstraint(t) &&
            t.getElementsOffsetsForTableCell(this).forEach((t) => {
              e.push(t);
            });
        });
      return e;
    }
  },
  Ds = class {
    constructor(e, t, i) {
      p(this, "startNodeContexts", []),
        p(this, "column"),
        (this.column = Object.create(e)),
        (this.column.element = t),
        (this.column.layoutContext = e.layoutContext.clone()),
        (this.column.stopAtOverflow = !1),
        (this.column.flowRootFormattingContext = i.formattingContext),
        (this.column.pseudoParent = e);
      let n = this.column.calculateClonedPaddingBorder(i);
      this.column.footnoteEdge = this.column.footnoteEdge - n;
      let r = this;
      this.column.openAllViews = function (e) {
        return Os.prototype.openAllViews
          .call(this, e)
          .thenAsync((e) => (r.startNodeContexts.push(e.copy()), T(e)));
      };
    }
    layout(e, t) {
      return this.column.layout(e, t);
    }
    findAcceptableBreakPosition(e) {
      let t = this.column.findAcceptableBreakPosition();
      if (e) {
        let e = this.startNodeContexts[0].copy(),
          i = new rn(e, null, e.overflow, 0);
        if ((i.findAcceptableBreak(this.column, 0), !t.nodeContext))
          return { breakPosition: i, nodeContext: e };
      }
      return t;
    }
    finishBreak(e, t, i) {
      return this.column.finishBreak(e, t, i);
    }
    doFinishBreakOfFragmentLayoutConstraints(e) {
      this.column.doFinishBreakOfFragmentLayoutConstraints(e);
    }
    isStartNodeContext(e) {
      let t = this.startNodeContexts[0];
      return (
        t.viewNode === e.viewNode &&
        t.after === e.after &&
        t.offsetInNode === e.offsetInNode
      );
    }
    isLastAfterNodeContext(e) {
      return Ut(e.toNodePosition(), this.column.lastAfterPosition);
    }
    getColumnElement() {
      return this.column.element;
    }
    getColumn() {
      return this.column;
    }
  },
  Ao = class {
    breakTextNode(e, t, i, n, r, s) {
      if (t.after) t.offsetInNode = e.length;
      else {
        let n = i - t.boxOffset,
          r = e.data;
        (n =
          173 == r.charCodeAt(n)
            ? this.breakAfterSoftHyphen(e, r, n, t)
            : this.breakAfterOtherCharacter(e, r, n, t)),
          n > 0 && (t = this.updateNodeContext(t, n, e));
      }
      return t;
    }
    breakAfterSoftHyphen(e, t, i, n) {
      let r =
        /[\u0620\u0626\u0628\u062A-\u062E\u0633-\u0647\u0649\u064A\u066E\u066F\u0678-\u0687\u069A-\u06BF\u06C1\u06C2\u06CC\u06CE\u06D0\u06D1\u06FA-\u06FC\u06FF\u0712-\u0714\u071A-\u071D\u071F-\u0727\u0729\u072B\u072D\u072E\u074E-\u0758\u075C-\u076A\u076D-\u0770\u0772\u0775-\u0777\u077A-\u077F\u07CA-\u07EA\u07FA\u0841-\u0845\u0848\u084A-\u0853\u0855\u0860\u0862-\u0865\u0868\u0883-\u0886\u0889-\u088D\u08A0-\u08A9\u08AF\u08B0\u08B3-\u08B8\u08BA-\u08C8\u1807\u180A\u1820-\u1878\u1887-\u18A8\u18AA\u200D\uA840-\uA871\u{10AC0}-\u{10AC4}\u{10AD3}-\u{10AD6}\u{10AD8}-\u{10ADC}\u{10ADE}-\u{10AE0}\u{10AEB}-\u{10AEE}\u{10B80}\u{10B82}\u{10B86}-\u{10B88}\u{10B8A}\u{10B8B}\u{10B8D}\u{10B90}\u{10BAD}\u{10BAE}\u{10D01}-\u{10D21}\u{10D23}\u{10EC3}\u{10EC4}\u{10F30}-\u{10F32}\u{10F34}-\u{10F44}\u{10F51}-\u{10F53}\u{10F70}-\u{10F73}\u{10F76}-\u{10F81}\u{10FB0}\u{10FB2}\u{10FB3}\u{10FB8}\u{10FBB}\u{10FBC}\u{10FBE}\u{10FBF}\u{10FC1}\u{10FC4}\u{10FCA}\u{1E900}-\u{1E943}]\p{Mn}*$/u.test(
          t.slice(0, i)
        )
          ? "‍"
          : "";
      if ((e.replaceData(i, t.length - i, n.breakWord ? "" : r + Df(n)), r)) {
        let e = n.preprocessedTextContent[0][1];
        n.preprocessedTextContent[0][1] =
          e.slice(0, i + 1) + r + e.slice(i + 1);
      }
      return i + 1;
    }
    breakAfterOtherCharacter(e, t, i, n) {
      let r = t.charAt(i);
      i++;
      let s = t.charAt(i);
      return (
        e.replaceData(
          i,
          t.length - i,
          !n.breakWord && Rl(r) && Rl(s) ? Df(n) : ""
        ),
        i
      );
    }
    updateNodeContext(e, t, i) {
      return ((e = e.modify()).offsetInNode += t), (e.breakBefore = null), e;
    }
  };
function Df(e) {
  return (
    e.hyphenateCharacter || (e.parent && e.parent.hyphenateCharacter) || "-"
  );
}
p(Ao, "instance"), (Ao.instance = new Ao());
var nd = class extends Yn {
    constructor(e, t) {
      super(),
        (this.leadingEdge = e),
        p(this, "breakAfter"),
        p(this, "initialPageBreakType", null),
        p(this, "initialComputedBlockSize", 0),
        p(this, "initialOverflown", !1),
        p(this, "context", { overflownNodeContext: null }),
        (this.breakAfter = t || null);
    }
    resolveLayoutMode(e) {
      return new sd(this.leadingEdge, this.breakAfter, this.context);
    }
    prepareLayout(e, t) {
      (t.fragmentLayoutConstraints = []), t.pseudoParent || cf();
    }
    clearNodes(e) {
      super.clearNodes(e);
      let t = e;
      for (; t; ) {
        let e = t.viewNode;
        e && ra(e.parentNode, e), (t = t.parent);
      }
    }
    saveState(e, t) {
      super.saveState(e, t),
        (this.initialPageBreakType = t.pageBreakType),
        (this.initialComputedBlockSize = t.computedBlockSize),
        (this.initialOverflown = t.overflown);
    }
    restoreState(e, t) {
      super.restoreState(e, t),
        (t.pageBreakType = this.initialPageBreakType),
        (t.computedBlockSize = this.initialComputedBlockSize),
        (t.overflown = this.initialOverflown);
    }
  },
  sd = class {
    constructor(e, t, i) {
      (this.leadingEdge = e), (this.breakAfter = t), (this.context = i);
    }
    doLayout(e, t) {
      let i = A("DefaultLayoutMode.doLayout");
      return (
        FC(e, t).then(() => {
          t.doLayout(e, this.leadingEdge, this.breakAfter).then((e) => {
            (this.context.overflownNodeContext = e.overflownNodeContext),
              i.finish(e.nodeContext);
          });
        }),
        i.result()
      );
    }
    accept(e, t) {
      return (
        !!(
          t.pageFloatLayoutContext.isInvalidated() ||
          t.pageBreakType ||
          t.fragmentLayoutConstraints.length <= 0
        ) ||
        t.fragmentLayoutConstraints.every((i) =>
          i.allowLayout(e, this.context.overflownNodeContext, t)
        )
      );
    }
    postLayout(e, t, i, n) {
      return (
        n || (n = !i.fragmentLayoutConstraints.some((t) => t.nextCandidate(e))),
        i.fragmentLayoutConstraints.forEach((r) => {
          r.postLayout(n, e, t, i);
        }),
        n
      );
    }
  },
  od = class extends Os {
    constructor(e, t, i, n, r, s, o) {
      super(t, i, n, r, s),
        (this.floatSide = e),
        (this.parentContainer = o),
        p(this, "rootViewNodes", []),
        p(this, "floatMargins", []),
        p(this, "adjustContentRelativeSize", !0);
    }
    openAllViews(e) {
      return super
        .openAllViews(e)
        .thenAsync((e) => (e && this.fixFloatSizeAndPosition(e), T(e)));
    }
    convertPercentageSizesToPx(e) {
      let t = this.parentContainer.getPaddingRect(),
        i = t.x2 - t.x1,
        n = t.y2 - t.y1;
      function r(t, i) {
        t.forEach((t) => {
          let n = Bt(e, t);
          if (n && "%" === n.charAt(n.length - 1)) {
            let r = parseFloat(n);
            w(e, t, `${(i * r) / 100}px`);
          }
        });
      }
      r(["width", "max-width", "min-width"], i),
        r(["height", "max-height", "min-height"], n),
        r(
          [
            "margin-top",
            "margin-right",
            "margin-bottom",
            "margin-left",
            "padding-top",
            "padding-right",
            "padding-bottom",
            "padding-left",
          ],
          this.vertical ? n : i
        ),
        ["margin-top", "margin-right", "margin-bottom", "margin-left"].forEach(
          (t) => {
            "auto" === Bt(e, t) && w(e, t, "0");
          }
        );
    }
    fixFloatSizeAndPosition(e) {
      for (; e.parent; ) e = e.parent;
      e.viewNode.nodeType;
      let t = e.viewNode;
      if (
        (this.rootViewNodes.push(t),
        this.adjustContentRelativeSize && this.convertPercentageSizesToPx(t),
        this.floatMargins.push(this.getComputedMargin(t)),
        this.adjustContentRelativeSize)
      ) {
        let e = this.floatSide;
        if (this.parentContainer.vertical) {
          if ("block-end" === e || "left" === e) {
            let e = Bt(t, "height");
            "" !== e && "auto" !== e && w(t, "margin-top", "auto");
          }
        } else if ("block-end" === e || "bottom" === e) {
          let e = Bt(t, "width");
          "" !== e && "auto" !== e && w(t, "margin-left", "auto");
        }
      }
    }
    getContentInlineSize() {
      return Math.max.apply(
        null,
        this.rootViewNodes.map((e, t) => {
          let i = vt(this.clientLayout, e, this.vertical),
            n = this.floatMargins[t];
          return this.vertical
            ? n.top + i.height + n.bottom
            : n.left + i.width + n.right;
        })
      );
    }
  },
  Wt = class {
    constructor(e, t) {
      (this.parent = e),
        (this.rootSourceNode = t),
        p(this, "formattingContextType", "RepetitiveElementsOwner"),
        p(this, "isRoot", !1),
        p(this, "repetitiveElements", null);
    }
    getName() {
      return "Repetitive elements owner formatting context (RepetitiveElementsOwnerFormattingContext)";
    }
    isFirstTime(e, t) {
      return t;
    }
    getParent() {
      return this.parent;
    }
    getRepetitiveElements() {
      return this.repetitiveElements;
    }
    getRootViewNode(e) {
      let t = this.getRootNodeContext(e);
      return t ? t.viewNode : null;
    }
    getRootNodeContext(e) {
      do {
        if (!e.belongsTo(this) && e.sourceNode === this.rootSourceNode)
          return e;
      } while ((e = e.parent));
      return null;
    }
    initializeRepetitiveElements(e) {
      this.repetitiveElements ||
        ca.some(
          (e) =>
            e.root === this.rootSourceNode &&
            ((this.repetitiveElements = e.elements), !0)
        ) ||
        ((this.repetitiveElements = new rd(e, this.rootSourceNode)),
        ca.push({
          root: this.rootSourceNode,
          elements: this.repetitiveElements,
        }));
    }
    saveState() {}
    restoreState(e) {}
  },
  rd = class {
    constructor(e, t) {
      (this.vertical = e),
        (this.ownerSourceNode = t),
        p(this, "headerSourceNode", null),
        p(this, "footerSourceNode", null),
        p(this, "headerViewNode", null),
        p(this, "footerViewNode", null),
        p(this, "headerNodePosition", null),
        p(this, "footerNodePosition", null),
        p(this, "headerHeight", 0),
        p(this, "footerHeight", 0),
        p(this, "isSkipHeader", !1),
        p(this, "isSkipFooter", !1),
        p(this, "enableSkippingFooter", !0),
        p(this, "enableSkippingHeader", !0),
        p(this, "doneInitialLayout", !1),
        p(this, "firstContentSourceNode", null),
        p(this, "lastContentSourceNode", null),
        p(this, "affectedNodeCache", []),
        p(this, "afterLastContentNodeCache", []),
        p(this, "allowInsert", !1),
        p(this, "allowInsertRepeatitiveElements");
    }
    setHeaderNodeContext(e) {
      this.headerNodePosition ||
        ((this.headerNodePosition = vs(e, 0)),
        (this.headerSourceNode = e.sourceNode),
        (this.headerViewNode = e.viewNode));
    }
    setFooterNodeContext(e) {
      this.footerNodePosition ||
        ((this.footerNodePosition = vs(e, 0)),
        (this.footerSourceNode = e.sourceNode),
        (this.footerViewNode = e.viewNode));
    }
    updateHeight(e) {
      this.headerViewNode &&
        ((this.headerHeight = pi(this.headerViewNode, e, this.vertical)),
        (this.headerViewNode = null)),
        this.footerViewNode &&
          ((this.footerHeight = pi(this.footerViewNode, e, this.vertical)),
          (this.footerViewNode = null));
    }
    prepareLayoutFragment() {
      (this.isSkipHeader = this.isSkipFooter = !1),
        (this.enableSkippingFooter = !0),
        (this.enableSkippingHeader = !0);
    }
    appendHeaderToFragment(e, t, i) {
      return !this.headerNodePosition || this.isSkipHeader
        ? T(!0)
        : this.appendElementToFragment(this.headerNodePosition, e, t, i);
    }
    appendFooterToFragment(e, t, i) {
      return !this.footerNodePosition || this.isSkipFooter
        ? T(!0)
        : this.appendElementToFragment(this.footerNodePosition, e, t, i);
    }
    appendElementToFragment(e, t, i, n) {
      let r = t.viewNode.ownerDocument,
        s = t.viewNode,
        o = r.createElement("div");
      s.appendChild(o);
      let a = new Ds(n, o, t),
        l = a.getColumn().pageBreakType;
      return (
        (a.getColumn().pageBreakType = null),
        (this.allowInsertRepeatitiveElements = !0),
        a
          .layout(new ht(e), !0)
          .thenAsync(
            () => (
              (this.allowInsertRepeatitiveElements = !1),
              s.removeChild(o),
              this.moveChildren(o, s, i),
              (a.getColumn().pageBreakType = l),
              T(!0)
            )
          )
      );
    }
    moveChildren(e, t, i) {
      if (t)
        for (; e.firstChild; ) {
          let n = e.firstChild;
          e.removeChild(n),
            n.setAttribute(Ht, "1"),
            i ? t.insertBefore(n, i) : t.appendChild(n);
        }
    }
    calculateOffset(e) {
      let t = 0;
      return (
        (e && !this.affectTo(e)) ||
          ((!this.isSkipFooter || (e && this.isAfterLastContent(e))) &&
            (t += this.footerHeight),
          this.isSkipHeader || (t += this.headerHeight)),
        t
      );
    }
    calculateMinimumOffset(e) {
      let t = 0;
      return (
        (e && !this.affectTo(e)) ||
          (!this.enableSkippingFooter &&
            e &&
            this.isAfterLastContent(e) &&
            (t += this.footerHeight),
          this.enableSkippingHeader || (t += this.headerHeight)),
        t
      );
    }
    isAfterLastContent(e) {
      return this.findResultFromCache(e, this.afterLastContentNodeCache, (t) =>
        this.isAfterNodeContextOf(this.lastContentSourceNode, e, !1)
      );
    }
    affectTo(e) {
      return this.findResultFromCache(e, this.affectedNodeCache, (t) =>
        this.isAfterNodeContextOf(this.ownerSourceNode, e, !0)
      );
    }
    findResultFromCache(e, t, i) {
      let n = t.filter(
        (t) =>
          t.nodeContext.sourceNode === e.sourceNode &&
          t.nodeContext.after === e.after
      );
      if (n.length > 0) return n[0].result;
      {
        let n = i(e);
        return t.push({ nodeContext: e, result: n }), n;
      }
    }
    isAfterNodeContextOf(e, t, i) {
      let n = [];
      for (let i = e; i; i = i.parentNode) {
        if (t.sourceNode === i) return t.after;
        n.push(i);
      }
      for (let e = t.sourceNode; e; e = e.parentNode) {
        let t = n.indexOf(e);
        if (t >= 0) return !!i && 0 === t;
        for (let t = e; t; t = t.previousElementSibling)
          if (n.includes(t)) return !0;
      }
      return t.after;
    }
    isFirstContentNode(e) {
      return e && this.firstContentSourceNode === e.sourceNode;
    }
    isEnableToUpdateState() {
      return !!(
        (!this.isSkipFooter &&
          this.enableSkippingFooter &&
          this.footerNodePosition) ||
        (!this.isSkipHeader &&
          this.enableSkippingHeader &&
          this.headerNodePosition)
      );
    }
    updateState() {
      !this.isSkipFooter && this.enableSkippingFooter && this.footerNodePosition
        ? (this.isSkipFooter = !0)
        : !this.isSkipHeader &&
          this.enableSkippingHeader &&
          this.headerNodePosition &&
          (this.isSkipHeader = !0);
    }
    preventSkippingHeader() {
      (this.isSkipHeader = !1), (this.enableSkippingHeader = !1);
    }
    preventSkippingFooter() {
      (this.isSkipFooter = !1), (this.enableSkippingFooter = !1);
    }
    isHeaderRegistered() {
      return !!this.headerNodePosition;
    }
    isFooterRegistered() {
      return !!this.footerNodePosition;
    }
    isHeaderSourceNode(e) {
      return this.headerSourceNode === e;
    }
    isFooterSourceNode(e) {
      return this.footerSourceNode === e;
    }
  },
  vi = class {
    constructor(e) {
      this.formattingContext = e;
    }
    accept(e, t) {
      return !!e;
    }
    postLayout(e, t, i, n) {
      let r = this.formattingContext.getRepetitiveElements();
      return (
        r &&
          (i.clientLayout,
          r.doneInitialLayout ||
            (r.updateHeight(i), (r.doneInitialLayout = !0))),
        n
      );
    }
  },
  Ti = class {
    constructor(e) {
      this.formattingContext = e;
    }
    accept(e, t) {
      return !0;
    }
    postLayout(e, t, i, n) {
      return n;
    }
  },
  ad = class extends vi {
    constructor(e, t) {
      super(e), (this.processor = t);
    }
    doLayout(e, t) {
      return this.processor.doInitialLayout(e, t);
    }
    accept(e, t) {
      return !1;
    }
  },
  ld = class extends Ti {
    constructor(e, t) {
      super(e), (this.processor = t);
    }
    doLayout(e, t) {
      return (
        !e.belongsTo(this.formattingContext) &&
          !e.after &&
          t.fragmentLayoutConstraints.unshift(new Lo(e)),
        this.processor.doLayout(e, t)
      );
    }
  },
  Lo = class e {
    constructor(e) {
      p(this, "flagmentLayoutConstraintType", "RepetitiveElementsOwner"),
        p(this, "nodeContext");
      let t = vn(e.formattingContext);
      this.nodeContext = t.getRootNodeContext(e);
    }
    allowLayout(e, t, i) {
      let n = this.getRepetitiveElements();
      return (
        !(n && !bn(this.nodeContext.viewNode) && n.isEnableToUpdateState()) ||
        !((t && !e) || (e && e.overflow))
      );
    }
    nextCandidate(e) {
      let t = this.getRepetitiveElements();
      return !(!t || !t.isEnableToUpdateState()) && (t.updateState(), !0);
    }
    postLayout(e, t, i, n) {
      let r = this.getRepetitiveElements();
      r &&
        e &&
        n.stopAtOverflow &&
        (null == t || r.isAfterLastContent(t)) &&
        r.preventSkippingFooter();
    }
    finishBreak(e, t) {
      let i = vn(this.nodeContext.formattingContext),
        n = this.getRepetitiveElements();
      if (!n) return T(!0);
      let r = this.nodeContext;
      return DC(i, r, t).thenAsync(() =>
        MC(i, r, t).thenAsync(() => (n.prepareLayoutFragment(), T(!0)))
      );
    }
    getRepetitiveElements() {
      return vn(this.nodeContext.formattingContext).getRepetitiveElements();
    }
    equalsTo(t) {
      return (
        t instanceof e &&
        vn(this.nodeContext.formattingContext) ===
          vn(t.nodeContext.formattingContext)
      );
    }
    getPriorityOfFinishBreak() {
      return 10;
    }
  },
  cd = class extends Yn {
    constructor(e, t) {
      super(), (this.formattingContext = e), (this.processor = t);
    }
    resolveLayoutMode(e) {
      let t = this.formattingContext.getRepetitiveElements();
      return e.belongsTo(this.formattingContext) || t.doneInitialLayout
        ? (!e.belongsTo(this.formattingContext) &&
            !e.after &&
            t &&
            t.preventSkippingHeader(),
          new ld(this.formattingContext, this.processor))
        : new ad(this.formattingContext, this.processor);
    }
  },
  ud = class extends yn {
    constructor(e, t) {
      super(), (this.formattingContext = e), (this.column = t);
    }
    startNonInlineElementNode(e) {
      let t = this.formattingContext,
        i = e.nodeContext,
        n = t.getRepetitiveElements();
      if (i.parent && t.rootSourceNode === i.parent.sourceNode) {
        switch (i.repeatOnBreak) {
          case "header":
            if (!n.isHeaderRegistered())
              return n.setHeaderNodeContext(i), T(!0);
            i.repeatOnBreak = "none";
            break;
          case "footer":
            if (!n.isFooterRegistered())
              return n.setFooterNodeContext(i), T(!0);
            i.repeatOnBreak = "none";
        }
        n.firstContentSourceNode || (n.firstContentSourceNode = i.sourceNode);
      }
      return yn.prototype.startNonInlineElementNode.call(this, e);
    }
    afterNonInlineElementNode(e) {
      let t = this.formattingContext,
        i = e.nodeContext;
      return (
        i.sourceNode === t.rootSourceNode &&
          ((t.getRepetitiveElements().lastContentSourceNode =
            e.lastAfterNodeContext && e.lastAfterNodeContext.sourceNode),
          (e.break = !0)),
        "header" === i.repeatOnBreak || "footer" === i.repeatOnBreak
          ? T(!0)
          : yn.prototype.afterNonInlineElementNode.call(this, e)
      );
    }
  },
  dd = class extends an {
    layout(e, t, i) {
      if (t.isFloatNodeContext(e)) return t.layoutFloatOrFootnote(e);
      let n = vn(e.formattingContext);
      return n.getRootViewNode(e)
        ? (i && pd(e.parent, t),
          e.belongsTo(n)
            ? an.prototype.layout.call(this, e, t, i)
            : new cd(n, this).layout(e, t))
        : t.buildDeepElementView(e);
    }
    startNonInlineElementNode(e) {
      let t = _C(e).getRepetitiveElements();
      return (
        t &&
          !t.allowInsertRepeatitiveElements &&
          (t.isHeaderSourceNode(e.sourceNode) ||
            t.isFooterSourceNode(e.sourceNode)) &&
          e.viewNode.parentNode.removeChild(e.viewNode),
        !1
      );
    }
    doInitialLayout(e, t) {
      vn(e.formattingContext);
      let i = A("BlockLayoutProcessor.doInitialLayout");
      return this.layoutEntireBlock(e, t).thenFinish(i), i.result();
    }
    layoutEntireBlock(e, t) {
      let i = vn(e.formattingContext),
        n = new ud(i, t);
      return new Ps(n, t.layoutContext).iterate(e);
    }
    doLayout(e, t) {
      let i = vn(e.formattingContext),
        n = A("doLayout");
      return (
        id(t.layoutContext.nextInTree(e, !1), t).then((e) => {
          let r = e;
          n.loopWithFrame((e) => {
            for (; r; ) {
              let n = !0;
              if (
                (t.layoutNext(r, !1).then((s) => {
                  (r = s),
                    t.pageFloatLayoutContext.isInvalidated() ||
                    t.pageBreakType ||
                    (r && t.stopByOverflow(r)) ||
                    (r && r.after && r.sourceNode == i.rootSourceNode)
                      ? e.breakLoop()
                      : n
                      ? (n = !1)
                      : e.continueLoop();
                }),
                n)
              )
                return void (n = !1);
            }
            e.breakLoop();
          }).then(() => {
            n.finish(r);
          });
        }),
        n.result()
      );
    }
    finishBreak(e, t, i, n) {
      return an.prototype.finishBreak.call(this, e, t, i, n);
    }
    clearOverflownViewNodes(e, t, i, n) {
      an.prototype.clearOverflownViewNodes(e, t, i, n);
    }
  };
function OC(e, t) {
  for (let i = e; i; i = i.parent) {
    let e = i.formattingContext;
    e && e instanceof Wt && !i.belongsTo(e) && t(e, i);
  }
}
function pd(e, t) {
  e &&
    OC(e.after ? e.parent : e, (e, i) => {
      _t.isInstanceOfTableFormattingContext(e) ||
        t.fragmentLayoutConstraints.push(new Lo(i));
    });
}
function DC(e, t, i) {
  let n = e.getRepetitiveElements();
  if (n) {
    let r = e.getRootNodeContext(t);
    if (r.viewNode) {
      let e = r.viewNode.firstChild;
      return n.appendHeaderToFragment(r, e, i);
    }
  }
  return T(!0);
}
function MC(e, t, i) {
  let n = e.getRepetitiveElements();
  if (n && !n.isSkipFooter) {
    let r = e.getRootNodeContext(t);
    if (r.viewNode) return n.appendFooterToFragment(r, null, i);
  }
  return T(!0);
}
function _C(e) {
  let t = e.formattingContext;
  return t && t instanceof Wt ? t : null;
}
function vn(e) {
  return e;
}
var UC = new dd();
Ue("RESOLVE_LAYOUT_PROCESSOR", (e) =>
  e instanceof Wt && !_t.isInstanceOfTableFormattingContext(e) ? UC : null
);
var ka = class {
    constructor(e, t) {
      (this.rowIndex = e), (this.sourceNode = t), p(this, "cells", []);
    }
    addCell(e) {
      this.cells.push(e);
    }
    getMinimumHeight() {
      return Math.min.apply(
        null,
        this.cells.map((e) => e.height)
      );
    }
  },
  fd = class {
    constructor(e, t, i) {
      (this.rowIndex = e),
        (this.columnIndex = t),
        p(this, "viewElement"),
        p(this, "colSpan"),
        p(this, "rowSpan"),
        p(this, "height", 0),
        p(this, "anchorSlot", null),
        (this.viewElement = i),
        (this.colSpan = i.colSpan || 1),
        (this.rowSpan = i.rowSpan || 1);
    }
    setHeight(e) {
      this.height = e;
    }
    setAnchorSlot(e) {
      this.anchorSlot = e;
    }
  },
  gd = class {
    constructor(e, t, i) {
      (this.rowIndex = e), (this.columnIndex = t), (this.cell = i);
    }
  },
  md = class {
    constructor(e, t, i) {
      (this.column = e),
        (this.cellNodeContext = i),
        p(this, "pseudoColumn"),
        p(this, "empty", !1),
        (this.pseudoColumn = new Ds(e, t, i));
    }
    findAcceptableBreakPosition() {
      let e = this.cellNodeContext.viewNode,
        { verticalAlign: t, alignContent: i } = e.style;
      "top" !== t && "baseline" !== t && w(e, "vertical-align", "top"),
        i && "normal" !== i && w(e, "align-content", "normal");
      let n = this.pseudoColumn.findAcceptableBreakPosition(!0);
      return (
        w(e, "vertical-align", t),
        i && "normal" !== i && w(e, "align-content", i),
        n
      );
    }
  },
  Cd = class {
    constructor(e, t) {
      (this.viewNode = e), (this.side = t);
    }
  },
  bd = class extends rn {
    constructor(e, t, i, n) {
      super(e, t, i, n),
        p(this, "formattingContext"),
        p(this, "acceptableCellBreakPositions", null),
        p(this, "rowIndex", null),
        (this.formattingContext = e.formattingContext);
    }
    findAcceptableBreak(e, t) {
      let i = super.findAcceptableBreak(e, t);
      return t < this.getMinBreakPenalty()
        ? null
        : this.getAcceptableCellBreakPositions().every((e) => !!e.nodeContext)
        ? i
        : null;
    }
    getMinBreakPenalty() {
      let e = super.getMinBreakPenalty();
      this.getAcceptableCellBreakPositions().forEach((t) => {
        e += t.breakPosition.getMinBreakPenalty();
      });
      let t = this.getCellFragments();
      return (
        (e += Math.max(0, ...t.map((e) => e.cellNodeContext.breakPenalty))), e
      );
    }
    getAcceptableCellBreakPositions() {
      if (!this.acceptableCellBreakPositions) {
        this.formattingContext;
        let e = this.getCellFragments();
        this.acceptableCellBreakPositions = e.map((e) =>
          e.findAcceptableBreakPosition()
        );
      }
      return this.acceptableCellBreakPositions;
    }
    getRowIndex() {
      return null != this.rowIndex
        ? this.rowIndex
        : (this.rowIndex = this.formattingContext.findRowIndexBySourceNode(
            this.position.sourceNode
          ));
    }
    getCellFragments() {
      return this.formattingContext
        .getRowSpanningCellsOverflowingTheRow(this.getRowIndex())
        .map(
          this.formattingContext.getCellFragmentOfCell,
          this.formattingContext
        );
    }
  },
  xd = class extends ws {
    constructor(e, t, i) {
      super(),
        (this.rowIndex = e),
        (this.beforeNodeContext = t),
        (this.formattingContext = i),
        p(this, "acceptableCellBreakPositions", null);
    }
    findAcceptableBreak(e, t) {
      if (this !== e.breakPositions[0] && t < this.getMinBreakPenalty())
        return null;
      let i = this.getCellFragments(),
        n = this.getAcceptableCellBreakPositions(),
        r =
          n.every((e) => !!e.nodeContext) &&
          n.some((e, t) => {
            let n = i[t].pseudoColumn,
              r = e.nodeContext;
            return !n.isStartNodeContext(r) && !n.isLastAfterNodeContext(r);
          });
      return (
        (this.beforeNodeContext.overflow = n.some(
          (e) => e.nodeContext && e.nodeContext.overflow
        )),
        r ? this.beforeNodeContext : null
      );
    }
    getMinBreakPenalty() {
      this.formattingContext.getRowByIndex(this.rowIndex);
      let e = this.beforeNodeContext.breakPenalty,
        t = this.getAcceptableCellBreakPositions(),
        i = this.getCellFragments();
      e += Math.max(0, ...i.map((e) => e.cellNodeContext.breakPenalty));
      let n = t.some(
        (e, t) =>
          e.breakPosition instanceof Bs &&
          i[t].cellNodeContext.sourceNode.rowSpan > 1
      );
      return (
        t.forEach((t, r) => {
          let s = t.breakPosition.getMinBreakPenalty();
          n &&
            t.breakPosition instanceof rn &&
            t.breakPosition.overflows &&
            s >= 3 &&
            (i[r].column.checkOverflowAndSaveEdge(t.nodeContext, null) ||
              (s -= 3)),
            (e += s);
        }),
        e
      );
    }
    getAcceptableCellBreakPositions() {
      if (!this.acceptableCellBreakPositions) {
        let e = this.getCellFragments();
        this.acceptableCellBreakPositions = e.map((e) =>
          e.findAcceptableBreakPosition()
        );
      }
      return this.acceptableCellBreakPositions;
    }
    getCellFragments() {
      return this.formattingContext
        .getCellsFallingOnRow(this.rowIndex)
        .map(
          this.formattingContext.getCellFragmentOfCell,
          this.formattingContext
        );
    }
  },
  Jn = class extends Wt {
    constructor(e, t) {
      super(e, t),
        (this.tableSourceNode = t),
        p(this, "formattingContextType", "Table"),
        p(this, "vertical", !1),
        p(this, "columnCount", -1),
        p(this, "tableWidth", 0),
        p(this, "captions", []),
        p(this, "colGroups", null),
        p(this, "colWidths", null),
        p(this, "inlineBorderSpacing", 0),
        p(this, "rows", []),
        p(this, "slots", []),
        p(this, "cellFragments", []),
        p(this, "lastRowViewNode", null),
        p(this, "cellBreakPositions", []),
        p(this, "repetitiveElements", null);
    }
    getName() {
      return "Table formatting context (Table.TableFormattingContext)";
    }
    isFirstTime(e, t) {
      if (!t) return t;
      switch (e.display) {
        case "table-row":
          return 0 === this.cellBreakPositions.length;
        case "table-cell":
          return !this.cellBreakPositions.some(
            (t) => t.cellNodePosition.steps[0].node === e.sourceNode
          );
        default:
          return t;
      }
    }
    getParent() {
      return this.parent;
    }
    finishFragment() {
      this.cellFragments = [];
    }
    addRow(e, t) {
      this.rows[e] = t;
    }
    getRowSlots(e) {
      let t = this.slots[e];
      return t || (t = this.slots[e] = []), t;
    }
    addCell(e, t) {
      let i = this.rows[e];
      i || (this.addRow(e, new ka(e, null)), (i = this.rows[e])), i.addCell(t);
      let n = e + t.rowSpan,
        r = this.getRowSlots(e),
        s = 0;
      for (; r[s]; ) s++;
      for (; e < n; e++) {
        r = this.getRowSlots(e);
        for (let i = s; i < s + t.colSpan; i++) {
          let n = (r[i] = new gd(e, i, t));
          t.anchorSlot || t.setAnchorSlot(n);
        }
      }
    }
    getRowByIndex(e) {
      return this.rows[e];
    }
    findRowIndexBySourceNode(e) {
      return this.rows.findIndex((t) => e === t.sourceNode);
    }
    addCellFragment(e, t, i) {
      let n = this.cellFragments[e];
      n || (n = this.cellFragments[e] = []), (n[t] = i);
    }
    getCellsFallingOnRow(e) {
      return this.getRowSlots(e).reduce(
        (e, t) => (t.cell !== e[e.length - 1] ? e.concat(t.cell) : e),
        []
      );
    }
    getRowSpanningCellsOverflowingTheRow(e) {
      return this.getCellsFallingOnRow(e).filter(
        (t) => t.rowIndex + t.rowSpan - 1 > e
      );
    }
    getCellFragmentOfCell(e) {
      return (
        this.cellFragments[e.rowIndex] &&
        this.cellFragments[e.rowIndex][e.columnIndex]
      );
    }
    getColumnCount() {
      return (
        this.columnCount < 0 &&
          (this.columnCount = Math.max.apply(
            null,
            this.rows.map((e) => e.cells.reduce((e, t) => e + t.colSpan, 0))
          )),
        this.columnCount
      );
    }
    updateCellSizes(e) {
      this.rows.forEach((t) => {
        t.cells.forEach((t) => {
          let i = vt(e, t.viewElement, this.vertical);
          (t.viewElement = null),
            t.setHeight(this.vertical ? i.width : i.height);
        });
      });
    }
    findCellFromColumn(e) {
      if (!e) return null;
      let t = null,
        i = 0,
        n = 0;
      e: for (i = 0; i < this.cellFragments.length; i++)
        if (this.cellFragments[i])
          for (n = 0; n < this.cellFragments[i].length; n++)
            if (
              this.cellFragments[i][n] &&
              e === this.cellFragments[i][n].pseudoColumn.getColumn()
            ) {
              t = this.rows[i].cells[n];
              break e;
            }
      if (!t) return null;
      for (; i < this.slots.length; i++)
        for (; n < this.slots[i].length; n++) {
          let e = this.slots[i][n];
          if (e.cell === t)
            return { rowIndex: e.rowIndex, columnIndex: e.columnIndex };
        }
      return null;
    }
    collectElementsOffsetOfUpperCells(e) {
      let t = [];
      return this.slots.reduce((i, n, r) => {
        if (r >= e.rowIndex) return i;
        let s =
          n[e.columnIndex] && this.getCellFragmentOfCell(n[e.columnIndex].cell);
        return (
          !s ||
            t.includes(s) ||
            (this.collectElementsOffsetFromColumn(
              s.pseudoColumn.getColumn(),
              i
            ),
            t.push(s)),
          i
        );
      }, []);
    }
    collectElementsOffsetOfHighestColumn() {
      let e = [];
      return (
        this.rows.forEach((t) => {
          t.cells.forEach((t, i) => {
            e[i] || (e[i] = { collected: [], elements: [] });
            let n = e[i],
              r = this.getCellFragmentOfCell(t);
            !r ||
              n.collected.includes(r) ||
              (this.collectElementsOffsetFromColumn(
                r.pseudoColumn.getColumn(),
                n.elements
              ),
              n.collected.push(r));
          });
        }),
        [new yd(e.map((e) => e.elements))]
      );
    }
    collectElementsOffsetFromColumn(e, t) {
      e.fragmentLayoutConstraints.forEach((e) => {
        if (on.isInstanceOfRepetitiveElementsOwnerLayoutConstraint(e)) {
          let i = e.getRepetitiveElements();
          t.push(i);
        }
        _t.isInstanceOfTableRowLayoutConstraint(e) &&
          e.getElementsOffsetsForTableCell(null).forEach((e) => {
            t.push(e);
          });
      });
    }
    saveState() {
      return [].concat(this.cellBreakPositions);
    }
    restoreState(e) {
      this.cellBreakPositions = e;
    }
  },
  yd = class {
    constructor(e) {
      this.repeatitiveElementsInColumns = e;
    }
    calculateOffset(e) {
      return this.calculateMaxOffsetOfColumn(e, (e) => e.current);
    }
    calculateMinimumOffset(e) {
      return this.calculateMaxOffsetOfColumn(e, (e) => e.minimum);
    }
    calculateMaxOffsetOfColumn(e, t) {
      let i = 0;
      return (
        this.repeatitiveElementsInColumns.forEach((n) => {
          let r = Xn(e, n);
          i = Math.max(i, t(r));
        }),
        i
      );
    }
  };
function It(e) {
  return e;
}
function HC(e) {
  return (
    "table-row-group" === e ||
    "table-header-group" === e ||
    "table-footer-group" === e
  );
}
function zC(e) {
  return "table" === e || "inline-table" === e;
}
function Hf(e) {
  return HC(e) || zC(e);
}
function zf(e, t, i) {
  let n = e.nodeContext,
    r = n.display,
    s = n.parent ? n.parent.display : null,
    o = !1;
  if ("inline-table" === s && !(n.formattingContext instanceof Jn))
    for (let e = n.parent; e; e = e.parent)
      if (e.formattingContext instanceof Jn) {
        o = e.formattingContext === t;
        break;
      }
  return o ||
    ("table-row" === r && !Hf(s)) ||
    ("table-cell" === r && "table-row" !== s && !Hf(s)) ||
    (n.formattingContext instanceof Jn && n.formattingContext !== t)
    ? i.buildDeepElementView(n).thenAsync((t) => ((e.nodeContext = t), T(!0)))
    : null;
}
var Ed = class extends yn {
    constructor(e, t) {
      super(),
        (this.formattingContext = e),
        (this.column = t),
        p(this, "rowIndex", -1),
        p(this, "columnIndex", 0),
        p(this, "inRow", !1),
        p(this, "checkPoints", []),
        p(this, "inHeaderOrFooter", !1);
    }
    startNonInlineElementNode(e) {
      let t = this.formattingContext,
        i = zf(e, t, this.column);
      if (i) return i;
      this.postLayoutBlockContents(e);
      let n = e.nodeContext,
        r = n.display,
        s = t.getRepetitiveElements();
      switch (r) {
        case "table":
          t.inlineBorderSpacing = n.inlineBorderSpacing;
          break;
        case "table-caption": {
          let e = new Cd(n.viewNode, n.captionSide);
          t.captions.push(e);
          break;
        }
        case "table-header-group":
          return (
            s.isHeaderRegistered() ||
              ((this.inHeaderOrFooter = !0), s.setHeaderNodeContext(n)),
            T(!0)
          );
        case "table-footer-group":
          return (
            s.isFooterRegistered() ||
              ((this.inHeaderOrFooter = !0), s.setFooterNodeContext(n)),
            T(!0)
          );
        case "table-row":
          this.inHeaderOrFooter ||
            ((this.inRow = !0),
            this.rowIndex++,
            n.sourceNode,
            (this.columnIndex = 0),
            t.addRow(this.rowIndex, new ka(this.rowIndex, n.sourceNode)),
            s.firstContentSourceNode ||
              (s.firstContentSourceNode = n.sourceNode));
      }
      return super.startNonInlineElementNode(e);
    }
    afterNonInlineElementNode(e) {
      let t = this.formattingContext,
        i = e.nodeContext,
        n = i.display,
        r = this.column.clientLayout;
      if (
        (this.postLayoutBlockContents(e), i.sourceNode === t.tableSourceNode)
      ) {
        let n = r.getElementComputedStyle(t.getRootViewNode(i));
        (t.tableWidth = parseFloat(n[t.vertical ? "height" : "width"])),
          (t.getRepetitiveElements().lastContentSourceNode =
            e.lastAfterNodeContext && e.lastAfterNodeContext.sourceNode),
          (e.break = !0);
      } else
        switch (n) {
          case "table-header-group":
          case "table-footer-group":
            if (this.inHeaderOrFooter)
              return (this.inHeaderOrFooter = !1), T(!0);
            break;
          case "table-row":
            this.inHeaderOrFooter ||
              ((t.lastRowViewNode = i.viewNode), (this.inRow = !1));
            break;
          case "table-cell":
            if (!this.inHeaderOrFooter) {
              this.inRow ||
                (this.rowIndex++, (this.columnIndex = 0), (this.inRow = !0));
              let e = i.viewNode;
              t.addCell(
                this.rowIndex,
                new fd(this.rowIndex, this.columnIndex, e)
              ),
                this.columnIndex++;
            }
        }
      return super.afterNonInlineElementNode(e);
    }
    startNonElementNode(e) {
      this.registerCheckPoint(e);
    }
    afterNonElementNode(e) {
      this.registerCheckPoint(e);
    }
    startInlineElementNode(e) {
      this.registerCheckPoint(e);
    }
    afterInlineElementNode(e) {
      this.registerCheckPoint(e);
    }
    registerCheckPoint(e) {
      let t = e.nodeContext;
      t && t.viewNode && !xn(t) && this.checkPoints.push(t.clone());
    }
    postLayoutBlockContents(e) {
      this.checkPoints.length > 0 &&
        this.column.postLayoutBlock(e.nodeContext, this.checkPoints),
        (this.checkPoints = []);
    }
  },
  La = class e extends yn {
    constructor(e, t) {
      super(!0),
        (this.formattingContext = e),
        (this.column = t),
        p(this, "inRow", !1),
        p(this, "currentRowIndex", -1),
        p(this, "currentColumnIndex", 0),
        p(this, "originalStopAtOverflow"),
        p(this, "inHeader"),
        p(this, "inFooter"),
        (this.originalStopAtOverflow = t.stopAtOverflow),
        (t.stopAtOverflow = !1);
    }
    resetColumn() {
      this.column.stopAtOverflow = this.originalStopAtOverflow;
    }
    getColSpanningCellWidth(e) {
      let t = this.formattingContext.colWidths,
        i = 0;
      for (let n = 0; n < e.colSpan; n++) i += t[e.anchorSlot.columnIndex + n];
      return (
        (i += this.formattingContext.inlineBorderSpacing * (e.colSpan - 1)), i
      );
    }
    layoutCell(e, t, i) {
      let n = e.rowIndex,
        r = e.columnIndex,
        s = e.colSpan,
        o = t.viewNode;
      t.verticalAlign;
      s > 1 &&
        (w(o, "box-sizing", "border-box"),
        w(
          o,
          this.formattingContext.vertical ? "height" : "width",
          `${this.getColSpanningCellWidth(e)}px`
        ));
      let a = o.ownerDocument.createElement("div");
      o.appendChild(a);
      let l = new md(this.column, a, t);
      return (
        this.formattingContext.addCellFragment(n, r, l),
        1 === i.primary.steps.length && i.primary.after && (l.empty = !0),
        l.pseudoColumn.layout(i, !0).thenReturn(!0)
      );
    }
    hasBrokenCellAtSlot(e) {
      let t = this.formattingContext.cellBreakPositions[0];
      return !!t && t.cell.anchorSlot.columnIndex === e;
    }
    extractRowSpanningCellBreakPositions() {
      let e = this.formattingContext.cellBreakPositions;
      if (0 === e.length) return [];
      let t = [],
        i = 0;
      do {
        let n = e[i],
          r = n.cell.rowIndex;
        if (r < this.currentRowIndex) {
          let s = t[r];
          s || (s = t[r] = []), s.push(n), e.splice(i, 1);
        } else i++;
      } while (i < e.length);
      return t;
    }
    layoutRowSpanningCellsFromPreviousFragment(e) {
      let t = this.formattingContext,
        i = this.extractRowSpanningCellBreakPositions(),
        n = i.reduce((e) => e + 1, 0);
      if (0 === n) return T(!0);
      let r = this.column.layoutContext,
        s = e.nodeContext;
      s.viewNode.parentNode.removeChild(s.viewNode);
      let o = A("layoutRowSpanningCellsFromPreviousFragment"),
        a = T(!0),
        l = 0,
        h = [];
      return (
        i.forEach((e) => {
          a = a.thenAsync(() => {
            let i = Co(e[0].cellNodePosition.steps[1], s.parent);
            return r.setCurrent(i, !1).thenAsync(() => {
              let s = T(!0),
                o = 0;
              function a(e) {
                for (; o < e; ) {
                  if (!h.includes(o)) {
                    let e = i.viewNode.ownerDocument.createElement("td");
                    w(e, "padding", "0"), i.viewNode.appendChild(e);
                  }
                  o++;
                }
              }
              return (
                e.forEach((e) => {
                  s = s.thenAsync(() => {
                    let t = e.cell;
                    a(t.anchorSlot.columnIndex);
                    let s = e.cellNodePosition,
                      u = Co(s.steps[0], i);
                    return (
                      (u.offsetInNode = s.offsetInNode),
                      (u.after = s.after),
                      (u.fragmentIndex = s.steps[0].fragmentIndex + 1),
                      r.setCurrent(u, !1).thenAsync(() => {
                        let i = e.breakChunkPosition;
                        for (let e = 0; e < t.colSpan; e++) h.push(o + e);
                        return (
                          (o += t.colSpan),
                          this.layoutCell(t, u, i).thenAsync(
                            () => (
                              (u.viewNode.rowSpan =
                                t.rowIndex +
                                t.rowSpan -
                                this.currentRowIndex +
                                n -
                                l),
                              T(!0)
                            )
                          )
                        );
                      })
                    );
                  });
                }),
                s.thenAsync(() => (a(t.getColumnCount()), l++, T(!0)))
              );
            });
          });
        }),
        a.then(() => {
          r.setCurrent(s, !0, e.atUnforcedBreak).then(() => {
            o.finish(!0);
          });
        }),
        o.result()
      );
    }
    startTableRow(e) {
      if (this.inHeader || this.inFooter) return T(!0);
      let t = e.nodeContext,
        i = this.formattingContext;
      return (
        this.currentRowIndex < 0
          ? (t.sourceNode,
            (this.currentRowIndex = i.findRowIndexBySourceNode(t.sourceNode)))
          : this.currentRowIndex++,
        (this.currentColumnIndex = 0),
        (this.inRow = !0),
        this.layoutRowSpanningCellsFromPreviousFragment(e).thenAsync(
          () => (
            this.registerCellFragmentIndex(),
            this.column.checkOverflowAndSaveEdgeAndBreakPosition(
              e.lastAfterNodeContext,
              null,
              !0,
              e.breakAtTheEdge
            ) &&
              0 ===
                i.getRowSpanningCellsOverflowingTheRow(this.currentRowIndex - 1)
                  .length &&
              (this.resetColumn(), (t.overflow = !0), (e.break = !0)),
            T(!0)
          )
        )
      );
    }
    registerCellFragmentIndex() {
      this.formattingContext
        .getRowByIndex(this.currentRowIndex)
        .cells.forEach((e) => {
          let t = this.formattingContext.cellBreakPositions[e.columnIndex];
          if (t && t.cell.anchorSlot.columnIndex == e.anchorSlot.columnIndex) {
            let e = t.cellNodePosition.steps[0],
              i = this.column.layoutContext.xmldoc.getElementOffset(e.node);
            Mf(i, e.fragmentIndex + 1, 1);
          }
        });
    }
    startTableCell(e) {
      if (this.inHeader || this.inFooter) return T(!0);
      let t = e.nodeContext;
      this.inRow ||
        (this.currentRowIndex < 0
          ? (this.currentRowIndex = 0)
          : this.currentRowIndex++,
        (this.currentColumnIndex = 0),
        (this.inRow = !0));
      let i = this.formattingContext.getRowByIndex(this.currentRowIndex).cells[
        this.currentColumnIndex
      ];
      if (!i) return (e.break = !0), T(!0);
      let n = t.copy().modify();
      (n.after = !0), (e.nodeContext = n);
      let r,
        s = A("startTableCell");
      if (this.hasBrokenCellAtSlot(i.anchorSlot.columnIndex)) {
        let e = this.formattingContext.cellBreakPositions.shift();
        (t.fragmentIndex = e.cellNodePosition.steps[0].fragmentIndex + 1),
          (r = T(e.breakChunkPosition));
      } else
        r = this.column.nextInTree(t, e.atUnforcedBreak).thenAsync((e) => {
          e.viewNode && t.viewNode.removeChild(e.viewNode);
          let i = vs(e, 0);
          return T(new ht(i));
        });
      return (
        r.then((n) => {
          this.layoutCell(i, t, n).then(() => {
            this.afterNonInlineElementNode(e),
              this.currentColumnIndex++,
              s.finish(!0);
          });
        }),
        s.result()
      );
    }
    startNonInlineBox(e) {
      let t = zf(e, It(this.formattingContext), this.column);
      if (t) return t;
      let i = e.nodeContext,
        n = this.formattingContext.getRepetitiveElements(),
        r = i.display;
      return "table-header-group" === r &&
        n &&
        n.isHeaderSourceNode(i.sourceNode)
        ? ((this.inHeader = !0), T(!0))
        : "table-footer-group" === r && n && n.isFooterSourceNode(i.sourceNode)
        ? ((this.inFooter = !0), T(!0))
        : "table-row" === r
        ? this.startTableRow(e)
        : "table-cell" === r
        ? this.startTableCell(e)
        : T(!0);
    }
    endNonInlineBox(e) {
      let t = e.nodeContext;
      if (
        "table-row" === t.display &&
        ((this.inRow = !1), !this.inHeader && !this.inFooter)
      ) {
        let e = t.copy().modify();
        e.after = !1;
        let i = new xd(this.currentRowIndex, e, this.formattingContext);
        this.column.breakPositions.push(i);
      }
      return T(!0);
    }
    afterNonInlineElementNode(t) {
      let i = t.nodeContext,
        n = this.formattingContext.getRepetitiveElements(),
        r = i.display;
      if (
        ("table-header-group" === r
          ? n &&
            !n.allowInsertRepeatitiveElements &&
            n.isHeaderSourceNode(i.sourceNode)
            ? ((this.inHeader = !1),
              i.viewNode.parentNode.removeChild(i.viewNode))
            : w(i.viewNode, "display", "table-row-group")
          : "table-footer-group" === r &&
            (n &&
            !n.allowInsertRepeatitiveElements &&
            n.isFooterSourceNode(i.sourceNode)
              ? ((this.inFooter = !1),
                i.viewNode.parentNode.removeChild(i.viewNode))
              : w(i.viewNode, "display", "table-row-group")),
        r && e.ignoreList[r])
      )
        i.viewNode.parentNode.removeChild(i.viewNode);
      else {
        if (i.sourceNode !== this.formattingContext.tableSourceNode)
          return super.afterNonInlineElementNode(t);
        (i.overflow = this.column.checkOverflowAndSaveEdge(i, null)),
          this.resetColumn(),
          (t.break = !0);
      }
      return T(!0);
    }
  };
p(La, "ignoreList", {
  "table-caption": !0,
  "table-column-group": !0,
  "table-column": !0,
});
var Sd = La,
  wi = [];
function GC(e) {
  let t = wi.findIndex((t) => t.root === e),
    i = wi[t];
  return i ? i.tableLayoutOption : null;
}
function WC(e) {
  let t = wi.findIndex((t) => t.root === e);
  t >= 0 && wi.splice(t, 1);
}
var Aa = class {
  layoutEntireTable(e, t) {
    let i = It(e.formattingContext),
      n = new Ed(i, t);
    return new Ps(n, t.layoutContext).iterate(e);
  }
  getColumnWidths(e, t, i, n) {
    let r = e.ownerDocument,
      s = r.createElement("tr"),
      o = [];
    for (let e = 0; e < t; e++) {
      let e = r.createElement("td");
      s.appendChild(e), o.push(e);
    }
    e.parentNode.insertBefore(s, e.nextSibling);
    let a = o.map((e) => {
      let t = vt(n, e, i),
        r = i ? t.height : t.width;
      return Math.ceil(r);
    });
    return e.parentNode.removeChild(s), a;
  }
  getColGroupElements(e) {
    let t = [],
      i = e.firstElementChild;
    for (; i; )
      "colgroup" === i.localName && t.push(i), (i = i.nextElementSibling);
    return t;
  }
  normalizeAndGetColElements(e) {
    let t = [];
    return (
      e.forEach((e) => {
        let i = e.span;
        e.removeAttribute("span");
        let n = e.firstElementChild;
        for (; n; ) {
          if ("col" === n.localName) {
            let r = n.span;
            for (n.removeAttribute("span"), i -= r; r-- > 1; ) {
              let i = n.cloneNode(!0);
              e.insertBefore(i, n), t.push(i);
            }
            t.push(n);
          }
          n = n.nextElementSibling;
        }
        for (; i-- > 0; )
          (n = e.ownerDocument.createElement("col")),
            e.appendChild(n),
            t.push(n);
      }),
      t
    );
  }
  addMissingColElements(e, t, i, n) {
    if (e.length < i) {
      let r = n.ownerDocument.createElement("colgroup");
      t.push(r);
      for (let t = e.length; t < i; t++) {
        let t = n.ownerDocument.createElement("col");
        r.appendChild(t), e.push(t);
      }
    }
  }
  normalizeColGroups(e, t, i) {
    let n = e.vertical,
      r = e.lastRowViewNode;
    if (!r) return;
    e.lastRowViewNode = null;
    let s = r.ownerDocument.createDocumentFragment(),
      o = e.getColumnCount();
    if (!(o > 0)) return void (e.colGroups = s);
    let a = (e.colWidths = this.getColumnWidths(r, o, n, i.clientLayout)),
      l = this.getColGroupElements(t),
      h = this.normalizeAndGetColElements(l);
    this.addMissingColElements(h, l, o, t),
      h.forEach((e, t) => {
        w(e, n ? "height" : "width", `${a[t]}px`);
      }),
      l.forEach((e) => {
        s.appendChild(e.cloneNode(!0));
      }),
      (e.colGroups = s);
  }
  doInitialLayout(e, t) {
    let i = It(e.formattingContext);
    (i.vertical = e.vertical),
      i.initializeRepetitiveElements(e.vertical),
      e.sourceNode;
    let n = GC(e.sourceNode);
    WC(e.sourceNode);
    let r = A("TableLayoutProcessor.doInitialLayout"),
      s = e.copy();
    return (
      this.layoutEntireTable(e, t).then((o) => {
        let a = o.viewNode,
          l = vt(t.clientLayout, a, t.vertical),
          h = t.vertical ? l.left : l.bottom;
        if (
          ((h +=
            (t.vertical ? -1 : 1) * Xn(e, t.collectElementsOffset()).current),
          !(t.isOverflown(h) || (n && n.calculateBreakPositionsInside)))
        )
          return t.breakPositions.push(new Td(s)), void r.finish(o);
        this.normalizeColGroups(i, a, t),
          i.updateCellSizes(t.clientLayout),
          r.finish(null);
      }),
      r.result()
    );
  }
  addCaptions(e, t, i) {
    let n = e.captions;
    n.forEach((e, r) => {
      e && (t.insertBefore(e.viewNode, i), "top" === e.side && (n[r] = null));
    });
  }
  addColGroups(e, t, i) {
    e.colGroups &&
      0 === this.getColGroupElements(t).length &&
      (t.insertBefore(e.colGroups.cloneNode(!0), i),
      w(t, "table-layout", "fixed"),
      w(t, e.vertical ? "height" : "width", `${e.tableWidth}px`));
  }
  removeColGroups(e, t) {
    if (e.colGroups && t) {
      let e = this.getColGroupElements(t);
      e &&
        e.forEach((e) => {
          t.removeChild(e);
        });
    }
  }
  doLayout(e, t) {
    let i = It(e.formattingContext),
      n = i.getRootViewNode(e),
      r = n.firstChild;
    this.addCaptions(i, n, r), this.addColGroups(i, n, r);
    let s = new Sd(i, t),
      o = new Ps(s, t.layoutContext),
      a = A("TableFormattingContext.doLayout");
    return o.iterate(e).thenFinish(a), a.result();
  }
  layout(e, t, i) {
    let n,
      r = It(e.formattingContext),
      s = r.getRootViewNode(e),
      o = A("TableFormattingContext.layout");
    return (
      s
        ? (i && pd(e.parent, t), (n = new Nd(r, this).layout(e, t)))
        : (n = t.buildDeepElementView(e)),
      n.then((e) => {
        o.finish(e),
          t.element.hasAttribute("data-vivliostyle-column-height-adjusted") &&
            (w(t.element, "width", `${t.width}px`),
            w(t.element, "height", `${t.height}px`),
            t.element.removeAttribute(
              "data-vivliostyle-column-height-adjusted"
            ));
      }),
      o.result()
    );
  }
  createEdgeBreakPosition(e, t, i, n) {
    return new bd(e, t, i, n);
  }
  startNonInlineElementNode(e) {
    return !1;
  }
  afterNonInlineElementNode(e, t) {
    return hd(e), !1;
  }
  finishBreak(e, t, i, n) {
    let r = It(t.formattingContext);
    if ("table-row" === t.display) {
      t.sourceNode;
      let i,
        n = r.findRowIndexBySourceNode(t.sourceNode);
      if (
        ((r.cellBreakPositions = []),
        (i = t.after
          ? r.getRowSpanningCellsOverflowingTheRow(n)
          : r.getCellsFallingOnRow(n)),
        i.length)
      ) {
        let s = A("TableLayoutProcessor.finishBreak"),
          o = 0;
        return (
          s
            .loopWithFrame((e) => {
              if (o === i.length) return void e.breakLoop();
              let t = i[o++],
                s = r.getCellFragmentOfCell(t),
                a = s.findAcceptableBreakPosition().nodeContext,
                l = s.cellNodeContext,
                h = l.toNodePosition(),
                u = new ht(a.toNodePosition());
              r.cellBreakPositions.push({
                cellNodePosition: h,
                breakChunkPosition: u,
                cell: t,
              });
              let c = l.viewNode;
              s.column.layoutContext.processFragmentedBlockEdge(
                s.cellNodeContext
              ),
                n < t.rowIndex + t.rowSpan - 1 &&
                  (c.rowSpan = n - t.rowIndex + 1),
                s.empty
                  ? e.continueLoop()
                  : s.pseudoColumn.finishBreak(a, !1, !0).then(() => {
                      $C(s, r, a), e.continueLoop();
                    });
            })
            .then(() => {
              e.clearOverflownViewNodes(t, !1),
                e.layoutContext.processFragmentedBlockEdge(t),
                hd(t),
                r.finishFragment(),
                s.finish(!0);
            }),
          s.result()
        );
      }
    }
    return hd(t), r.finishFragment(), hi.finishBreak(e, t, i, n);
  }
  clearOverflownViewNodes(e, t, i, n) {
    an.prototype.clearOverflownViewNodes(e, t, i, n);
  }
};
function $C(e, t, i) {
  let n = t.getRepetitiveElements();
  if (!n || (!n.isHeaderRegistered() && !n.isFooterRegistered())) return;
  let r = t.vertical,
    s = e.column,
    o = e.pseudoColumn.getColumnElement(),
    a = e.cellNodeContext.viewNode,
    l = vt(s.clientLayout, a, r),
    h = s.getComputedPaddingBorder(a);
  if (r) {
    w(
      o,
      "max-width",
      `${l.right - s.footnoteEdge - n.calculateOffset(i) - h.right}px`
    );
  } else {
    w(
      o,
      "max-height",
      `${s.footnoteEdge - n.calculateOffset(i) - l.top - h.top}px`
    );
  }
}
function hd(e) {
  let t,
    i =
      "table-row" === e.display
        ? e.viewNode.parentElement
        : "table" === e.display
        ? e.viewNode.querySelector("tbody")
        : null;
  if (!i) return;
  try {
    t = i.querySelectorAll(
      ":scope>tr:has(>:empty):not(:has(>:not([rowspan]:not([rowspan='1']),:empty)))"
    );
  } catch (e) {
    return;
  }
  if (0 === t.length) return;
  let n = Array.from(t).reduce((t, i) => {
      let n = i.getBoundingClientRect();
      return t + (e.vertical ? n.width : n.height);
    }, 0),
    r = t[t.length - 1],
    s = Array.from(i.children).indexOf(r),
    o = Array.from(r.children).reduce((e, t) => {
      let n = t.rowSpan;
      return n > 1 && s + n < i.childElementCount ? Math.max(e, n) : e;
    }, 0),
    a = o ? i.children[s + o - 1] : i.lastElementChild;
  if (a != i.lastElementChild && a.querySelector(":scope>*>div>div"))
    for (let e = a; e && e !== i.lastElementChild; e = e.nextElementSibling)
      if (e.querySelector(":scope>[rowspan]:not([rowspan='1'])")) {
        a = e;
        break;
      }
  let l = a.getBoundingClientRect(),
    h = n + (e.vertical ? l.width : l.height);
  w(a, e.vertical ? "width" : "height", `${h}px`);
}
var Nd = class extends Yn {
    constructor(e, t) {
      super(), (this.tableFormattingContext = e), (this.processor = t);
    }
    resolveLayoutMode(e) {
      let t = this.tableFormattingContext.getRepetitiveElements();
      return t && t.doneInitialLayout
        ? (e.sourceNode === this.tableFormattingContext.tableSourceNode &&
            !e.after &&
            t &&
            t.preventSkippingHeader(),
          new Pd(this.tableFormattingContext, this.processor))
        : new vd(this.tableFormattingContext, this.processor);
    }
    clearNodes(e) {
      super.clearNodes(e);
      let t = this.tableFormattingContext.getRootViewNode(e);
      this.processor.removeColGroups(this.tableFormattingContext, t);
    }
    restoreState(e, t) {
      super.restoreState(e, t), this.tableFormattingContext.finishFragment();
    }
  },
  vd = class extends vi {
    constructor(e, t) {
      super(e), (this.processor = t);
    }
    doLayout(e, t) {
      return this.processor.doInitialLayout(e, t);
    }
  },
  Td = class extends rn {
    constructor(e) {
      super(e, null, e.overflow, 0);
    }
    getMinBreakPenalty() {
      if (!this.isEdgeUpdated)
        throw new Error("EdgeBreakPosition.prototype.updateEdge not called");
      return (
        (this.overflows ? 3 : 0) +
        (this.position.parent ? this.position.parent.breakPenalty : 0)
      );
    }
    breakPositionChosen(e) {
      e.fragmentLayoutConstraints.push(new wd(this.position.sourceNode));
    }
  },
  wd = class e {
    constructor(e) {
      (this.tableRootNode = e),
        p(this, "flagmentLayoutConstraintType", "EntireTable");
    }
    allowLayout(e, t, i) {
      return e.overflow, !1;
    }
    nextCandidate(e) {
      return !0;
    }
    postLayout(e, t, i, n) {
      t.sourceNode,
        wi.push({
          root: t.sourceNode,
          tableLayoutOption: { calculateBreakPositionsInside: !0 },
        });
    }
    finishBreak(e, t) {
      return T(!0);
    }
    equalsTo(t) {
      return t instanceof e && t.tableRootNode === this.tableRootNode;
    }
    getPriorityOfFinishBreak() {
      return 0;
    }
  },
  Pd = class extends Ti {
    constructor(e, t) {
      super(e), (this.processor = t);
    }
    doLayout(e, t) {
      let i = this.formattingContext.getRepetitiveElements();
      if (i && !i.isAfterLastContent(e)) {
        let i = new kd(e);
        t.fragmentLayoutConstraints.some((e) => i.equalsTo(e)) ||
          t.fragmentLayoutConstraints.unshift(i);
      }
      return this.processor.doLayout(e, t);
    }
  },
  kd = class e extends Lo {
    constructor(e) {
      super(e),
        p(this, "flagmentLayoutConstraintType", "TableRow"),
        p(this, "cellFragmentLayoutConstraints", []);
    }
    allowLayout(e, t, i) {
      let n = this.getRepetitiveElements();
      return (
        !(
          n &&
          !i.pseudoParent &&
          !bn(this.nodeContext.viewNode) &&
          n.isEnableToUpdateState()
        ) || !((t && !e) || (e && e.overflow))
      );
    }
    nextCandidate(e) {
      let t = It(this.nodeContext.formattingContext);
      return (
        !!this.collectCellFragmentLayoutConstraints(e, t).some((t) =>
          t.constraints.some((t) => t.nextCandidate(e))
        ) || super.nextCandidate(e)
      );
    }
    postLayout(e, t, i, n) {
      let r = It(this.nodeContext.formattingContext);
      if (
        ((this.cellFragmentLayoutConstraints =
          this.collectCellFragmentLayoutConstraints(t, r)),
        this.cellFragmentLayoutConstraints.forEach((t) => {
          t.constraints.forEach((r) => {
            r.postLayout(e, t.breakPosition, i, n);
          });
        }),
        !e)
      ) {
        let e = r.getRootViewNode(this.nodeContext);
        new Aa().removeColGroups(r, e), this.removeDummyRowNodes(i);
      }
      super.postLayout(e, t, i, n);
    }
    finishBreak(e, t) {
      It(this.nodeContext.formattingContext);
      let i = A("finishBreak"),
        n = this.cellFragmentLayoutConstraints.reduce(
          (e, t) =>
            e.concat(
              t.constraints.map((e) => ({
                constraint: e,
                breakPosition: t.breakPosition,
              }))
            ),
          []
        ),
        r = 0;
      return (
        i
          .loop(() => {
            if (r < n.length) {
              let e = n[r++];
              return e.constraint
                .finishBreak(e.breakPosition, t)
                .thenReturn(!0);
            }
            return T(!1);
          })
          .then(() => {
            i.finish(!0);
          }),
        i.result().thenAsync(() => super.finishBreak(e, t))
      );
    }
    removeDummyRowNodes(e) {
      if (e && "table-row" === e.display && e.viewNode)
        for (; e.viewNode.previousElementSibling; ) {
          let t = e.viewNode.previousElementSibling;
          t.parentNode && t.parentNode.removeChild(t);
        }
    }
    collectCellFragmentLayoutConstraints(e, t) {
      return this.getCellFragemnts(e, t).map((e) => ({
        constraints:
          e.fragment.pseudoColumn.getColumn().fragmentLayoutConstraints,
        breakPosition: e.breakPosition,
      }));
    }
    getCellFragemnts(e, t) {
      let i = Number.MAX_VALUE;
      e &&
        "table-row" === e.display &&
        (e.sourceNode, (i = t.findRowIndexBySourceNode(e.sourceNode) + 1)),
        (i = Math.min(t.cellFragments.length, i));
      let n = [];
      for (let e = 0; e < i; e++)
        t.cellFragments[e] &&
          t.cellFragments[e].forEach((e) => {
            e &&
              n.push({
                fragment: e,
                breakPosition: e.findAcceptableBreakPosition().nodeContext,
              });
          });
      return n;
    }
    getElementsOffsetsForTableCell(e) {
      let t = It(this.nodeContext.formattingContext),
        i = t.findCellFromColumn(e);
      return i
        ? t.collectElementsOffsetOfUpperCells(i)
        : t.collectElementsOffsetOfHighestColumn();
    }
    equalsTo(t) {
      return (
        t instanceof e &&
        It(this.nodeContext.formattingContext) ===
          It(t.nodeContext.formattingContext)
      );
    }
  },
  XC = new Aa();
function jC(e, t, i, n, r, s) {
  if (!t) return null;
  if (i === b.table) {
    let t = e.parent;
    return new Jn(t ? t.formattingContext : null, e.sourceNode);
  }
  return null;
}
function YC(e) {
  return e instanceof Jn ? XC : null;
}
function Gf(e) {
  return e.reduce((e, t) => e + t, 0) / e.length;
}
function Wf(e) {
  let t = Gf(e);
  return Gf(
    e.map((e) => {
      let i = e - t;
      return i * i;
    })
  );
}
Ue("RESOLVE_FORMATTING_CONTEXT", jC), Ue("RESOLVE_LAYOUT_PROCESSOR", YC);
var Ad = class {
  constructor(e, t) {
    (this.layoutResult = e), (this.penalty = t);
  }
};
function Pi(e) {
  return e.vertical ? e.width : e.height;
}
function ki(e, t) {
  e.vertical ? (e.width = t) : (e.height = t);
}
var Ra = class {
    constructor(e, t, i) {
      (this.layoutContainer = e),
        (this.columnGenerator = t),
        (this.regionPageFloatLayoutContext = i),
        p(this, "originalContainerBlockSize"),
        (this.originalContainerBlockSize = Pi(e));
    }
    balanceColumns(e) {
      let t = A("ColumnBalancer#balanceColumns");
      this.preBalance(e),
        this.savePageFloatLayoutContexts(e),
        this.layoutContainer.clear();
      let i = [this.createTrialResult(e)];
      return (
        t
          .loopWithFrame((e) => {
            this.hasNextCandidate(i)
              ? (this.updateCondition(i),
                this.columnGenerator().then((t) => {
                  this.savePageFloatLayoutContexts(t),
                    this.layoutContainer.clear(),
                    t
                      ? (i.push(this.createTrialResult(t)), e.continueLoop())
                      : e.breakLoop();
                }))
              : e.breakLoop();
          })
          .then(() => {
            let e = i.reduce((e, t) => (t.penalty < e.penalty ? t : e), i[0]);
            this.restoreContents(e.layoutResult),
              this.postBalance(),
              t.finish(e.layoutResult);
          }),
        t.result()
      );
    }
    createTrialResult(e) {
      let t = this.calculatePenalty(e);
      return new Ad(e, t);
    }
    preBalance(e) {}
    postBalance() {
      ki(this.layoutContainer, this.originalContainerBlockSize);
    }
    savePageFloatLayoutContexts(e) {
      let t = this.regionPageFloatLayoutContext.detachChildren();
      e && (e.columnPageFloatLayoutContexts = t);
    }
    restoreContents(e) {
      let t = this.layoutContainer.element;
      e.columns.forEach((e) => {
        t.appendChild(e.element);
      }),
        e.columnPageFloatLayoutContexts,
        this.regionPageFloatLayoutContext.attachChildren(
          e.columnPageFloatLayoutContexts
        );
    }
  },
  Ld = 1;
function Xf(e) {
  let t = e[e.length - 1];
  if (0 === t.penalty) return !1;
  let i = e[e.length - 2];
  if (i && t.penalty >= i.penalty) return !1;
  let n = t.layoutResult.columns;
  return (
    Math.max.apply(
      null,
      n.map((e) => e.computedBlockSize)
    ) >
    Math.max.apply(
      null,
      n.map((e) => e.getMaxBlockSizeOfPageFloats())
    ) +
      Ld
  );
}
function jf(e, t) {
  var i;
  let n = e[e.length - 1].layoutResult.columns,
    r =
      Math.max.apply(
        null,
        n.map((e) =>
          isNaN(e.blockDistanceToBlockEndFloats)
            ? e.computedBlockSize
            : e.computedBlockSize - e.blockDistanceToBlockEndFloats + Ld
        )
      ) - Ld;
  if ((r < Pi(t) ? ki(t, r) : ki(t, Pi(t) - 1), t.vertical)) {
    let e = parseFloat(null == (i = t.element.style) ? void 0 : i.width);
    t.originX = e - t.width;
  }
}
var Rd = class extends Ra {
  constructor(e, t, i, n) {
    super(i, e, t),
      (this.columnCount = n),
      p(this, "originalPosition", null),
      p(this, "foundUpperBound", !1);
  }
  preBalance(e) {
    let t = e.columns.reduce((e, t) => e + t.computedBlockSize, 0);
    ki(this.layoutContainer, t / this.columnCount),
      (this.originalPosition = e.position);
  }
  checkPosition(e) {
    return this.originalPosition
      ? this.originalPosition.isSamePosition(e)
      : null === e;
  }
  calculatePenalty(e) {
    if (!this.checkPosition(e.position)) return 1 / 0;
    let t = e.columns;
    return $f(t)
      ? 1 / 0
      : Math.max.apply(
          null,
          t.map((e) => e.computedBlockSize)
        );
  }
  hasNextCandidate(e) {
    if (1 === e.length) return !0;
    if (this.foundUpperBound) return Xf(e);
    {
      let t = e[e.length - 1];
      return this.checkPosition(t.layoutResult.position) &&
        !$f(t.layoutResult.columns)
        ? ((this.foundUpperBound = !0), !0)
        : Pi(this.layoutContainer) < this.originalContainerBlockSize;
    }
  }
  updateCondition(e) {
    if (this.foundUpperBound) jf(e, this.layoutContainer);
    else {
      let e = Math.min(
        this.originalContainerBlockSize,
        Pi(this.layoutContainer) + 0.1 * this.originalContainerBlockSize
      );
      ki(this.layoutContainer, e);
    }
  }
};
function $f(e) {
  if (e.length <= 1) return !1;
  let t = e[e.length - 1].computedBlockSize,
    i = e.slice(0, e.length - 1);
  return i.every((e) => t > e.computedBlockSize + 6);
}
var Id = class extends Ra {
  constructor(e, t, i) {
    super(i, e, t);
  }
  calculatePenalty(e) {
    if (e.columns.every((e) => 0 === e.computedBlockSize)) return 1 / 0;
    return Wf(
      e.columns.filter((e) => !e.pageBreakType).map((e) => e.computedBlockSize)
    );
  }
  hasNextCandidate(e) {
    return Xf(e);
  }
  updateCondition(e) {
    jf(e, this.layoutContainer);
  }
};
function Yf(e, t, i, n, r, s, o) {
  if (t === b.auto) return null;
  {
    let a = 0 === o.positions.length,
      l = s[s.length - 1],
      h = !(!l || !l.pageBreakType);
    return a || h
      ? new Rd(i, n, r, e)
      : t === b.balance_all
      ? new Id(i, n, r)
      : (b.balance, null);
  }
}
var Ai = class {
    constructor(e, t, i) {
      p(this, "endStuckFixed"),
        p(this, "endFixed"),
        p(this, "endSlipped"),
        (this.endStuckFixed = e),
        (this.endFixed = t),
        (this.endSlipped = i);
    }
  },
  Vd = class {
    constructor() {
      p(this, "map", []);
    }
    getMaxFixed() {
      return 0 == this.map.length ? 0 : this.map[this.map.length - 1].endFixed;
    }
    getMaxSlipped() {
      return 0 == this.map.length
        ? 0
        : this.map[this.map.length - 1].endSlipped;
    }
    addStuckRange(e) {
      if (0 == this.map.length) this.map.push(new Ai(e, e, e));
      else {
        let t = this.map[this.map.length - 1],
          i = t.endSlipped + e - t.endFixed;
        t.endFixed == t.endStuckFixed
          ? ((t.endFixed = e), (t.endStuckFixed = e), (t.endSlipped = i))
          : this.map.push(new Ai(e, e, i));
      }
    }
    addSlippedRange(e) {
      0 == this.map.length
        ? this.map.push(new Ai(e, 0, 0))
        : (this.map[this.map.length - 1].endFixed = e);
    }
    slippedByFixed(e) {
      let t = gt(this.map.length, (t) => e <= this.map[t].endFixed),
        i = this.map[t];
      return i.endSlipped - Math.max(0, i.endStuckFixed - e);
    }
    fixedBySlipped(e) {
      let t = gt(this.map.length, (t) => e <= this.map[t].endSlipped),
        i = this.map[t];
      return i.endStuckFixed - (i.endSlipped - e);
    }
  },
  Fd = class e {
    constructor(t, i, n, r, s, o, a, l) {
      if (
        ((this.context = t),
        (this.style = i),
        (this.offset = n),
        (this.isRoot = r),
        (this.flowChunk = s),
        (this.atBlockStart = o),
        (this.atFlowStart = a),
        (this.isParentBoxDisplayed = l),
        p(this, "flowName"),
        p(this, "isBlockValue", null),
        p(this, "hasBoxValue", null),
        p(this, "styleValues", {}),
        p(this, "beforeBox", null),
        p(this, "afterBox", null),
        p(this, "breakBefore", null),
        (this.flowName = s.flowName),
        this.hasBox())
      ) {
        let r = i._pseudos;
        if (r && r.before) {
          let i = new e(t, r.before, n, !1, s, this.isBlock(), a, !0);
          St(i.styleValue("content")) &&
            ((this.beforeBox = i), (this.breakBefore = i.breakBefore));
        }
      }
      (this.breakBefore = Ve(this.getBreakValue("before"), this.breakBefore)),
        this.atFlowStart &&
          Ie(this.breakBefore) &&
          (s.breakBefore = Ve(s.breakBefore, this.breakBefore));
    }
    buildAfterPseudoElementBox(t, i, n) {
      if (this.hasBox()) {
        let r = this.style._pseudos;
        if (r && r.after) {
          let s = new e(this.context, r.after, t, !1, this.flowChunk, i, n, !0);
          St(s.styleValue("content")) && (this.afterBox = s);
        }
      }
    }
    styleValue(e, t) {
      if (!(e in this.styleValues)) {
        let i = this.style[e];
        this.styleValues[e] = i ? i.evaluate(this.context, e) : t || null;
      }
      return this.styleValues[e];
    }
    displayValue() {
      return this.styleValue("display", b.inline);
    }
    isBlock() {
      if (null === this.isBlockValue) {
        let e = this.displayValue(),
          t = this.styleValue("position"),
          i = this.styleValue("float");
        this.isBlockValue = oa(e, t, i, this.isRoot);
      }
      return this.isBlockValue;
    }
    hasBox() {
      return (
        null === this.hasBoxValue &&
          (this.hasBoxValue =
            this.isParentBoxDisplayed &&
            this.displayValue() !== b.none &&
            !ci(this.styleValue("position"))),
        this.hasBoxValue
      );
    }
    getBreakValue(e) {
      let t = null;
      if (this.isBlock()) {
        let i = this.styleValue(`break-${e}`);
        i && (t = i.toString());
      }
      return t;
    }
  },
  Bd = class {
    constructor(e) {
      (this.context = e),
        p(this, "stack", []),
        p(this, "atBlockStart", !0),
        p(this, "atFlowStart", !0),
        p(this, "atStartStack", []);
    }
    empty() {
      return 0 === this.stack.length;
    }
    lastBox() {
      return this.stack[this.stack.length - 1];
    }
    lastFlowName() {
      let e = this.lastBox();
      return e ? e.flowChunk.flowName : null;
    }
    isCurrentBoxDisplayed() {
      return this.stack.every((e) => e.displayValue() !== b.none);
    }
    push(e, t, i, n) {
      let r = this.lastBox();
      n &&
        r &&
        n.flowName !== r.flowName &&
        this.atStartStack.push({
          atBlockStart: this.atBlockStart,
          atFlowStart: this.atFlowStart,
        });
      let s = n || r.flowChunk,
        o = this.atFlowStart || !!n,
        a = this.isCurrentBoxDisplayed(),
        l = new Fd(this.context, e, t, i, s, o || this.atBlockStart, o, a);
      return (
        this.stack.push(l),
        (this.atBlockStart = l.hasBox()
          ? !l.beforeBox && l.isBlock()
          : this.atBlockStart),
        (this.atFlowStart = l.hasBox() ? !l.beforeBox && o : this.atFlowStart),
        l
      );
    }
    encounteredTextNode(e) {
      let t = this.lastBox();
      if (
        (e.nodeType === Node.TEXT_NODE ||
          e.nodeType === Node.CDATA_SECTION_NODE) &&
        (this.atBlockStart || this.atFlowStart) &&
        t.hasBox()
      ) {
        let i = ea(t.styleValue("white-space", b.normal).toString());
        i && !de(e, i) && ((this.atBlockStart = !1), (this.atFlowStart = !1));
      }
    }
    pop(e) {
      let t = this.stack.pop();
      if (
        (t.buildAfterPseudoElementBox(e, this.atBlockStart, this.atFlowStart),
        this.atFlowStart && t.afterBox)
      ) {
        let e = t.afterBox.getBreakValue("before");
        t.flowChunk.breakBefore = Ve(t.flowChunk.breakBefore, e);
      }
      let i = this.lastBox();
      if (i)
        if (i.flowName === t.flowName)
          t.hasBox() && (this.atBlockStart = this.atFlowStart = !1);
        else {
          let e = this.atStartStack.pop();
          (this.atBlockStart = e.atBlockStart),
            (this.atFlowStart = e.atFlowStart);
        }
      return t;
    }
    nearestBlockStartOffset(e) {
      if (!e.atBlockStart) return e.offset;
      let t = this.stack.length - 1,
        i = this.stack[t];
      for (i === e && (t--, (i = this.stack[t])); t >= 0; ) {
        if (i.flowName !== e.flowName) return e.offset;
        if (!i.atBlockStart || i.isRoot) return i.offset;
        (e = i), (i = this.stack[--t]);
      }
      throw new Error("No block start offset found!");
    }
  },
  Li = class {
    constructor(e, t, i, n, r, s, o, a) {
      (this.xmldoc = e),
        (this.scope = i),
        (this.context = n),
        (this.primaryFlows = r),
        (this.validatorSet = s),
        (this.counterListener = o),
        p(this, "root"),
        p(this, "cascadeHolder"),
        p(this, "last"),
        p(this, "rootStyle", {}),
        p(this, "styleMap", {}),
        p(this, "flows", {}),
        p(this, "flowChunks", []),
        p(this, "flowListener", null),
        p(this, "flowToReach", null),
        p(this, "idToReach", null),
        p(this, "cascade"),
        p(this, "offsetMap"),
        p(this, "primary", !0),
        p(this, "primaryStack", []),
        p(this, "rootBackgroundAssigned", !1),
        p(this, "rootLayoutAssigned", !1),
        p(this, "lastOffset"),
        p(this, "breakBeforeValues", {}),
        p(this, "boxStack"),
        p(this, "bodyReached", !0),
        (this.root = e.root),
        (this.cascadeHolder = t),
        (this.last = this.root),
        (this.cascade = t.createInstance(n, o, a, e.lang)),
        (this.offsetMap = new Vd());
      let l = e.getElementOffset(this.root);
      (this.lastOffset = l),
        (this.boxStack = new Bd(n)),
        this.offsetMap.addStuckRange(l);
      let h = this.getAttrStyle(this.root);
      if (
        "http://www.w3.org/1999/xhtml" ===
        (this.cascade.pushElement(this, this.root, h, l),
        this.postprocessTopStyle(h, !1),
        this.root.namespaceURI)
      )
        this.bodyReached = !1;
      this.primaryStack.push(!0),
        (this.styleMap = {}),
        (this.styleMap[`e${l}`] = h),
        this.lastOffset++,
        this.replayFlowElementsFromOffset(-1);
    }
    hasProp(e, t, i) {
      let n = e[i];
      return n && n.evaluate(this.context) !== t[i];
    }
    transferPropsToRoot(e, t) {
      for (let i in t) {
        let n = e[i];
        if (n) (this.rootStyle[i] = n), delete e[i];
        else {
          let e = t[i];
          e && (this.rootStyle[i] = new _(e, Tc));
        }
      }
    }
    postprocessTopStyle(e, t) {
      var i;
      if (t)
        for (let i of ["writing-mode", "direction"])
          e[i] && (!t || !this.rootStyle[i]) && (this.rootStyle[i] = e[i]);
      else for (let t in e) Zn(t) && (this.rootStyle[t] = e[t]);
      if (!this.rootBackgroundAssigned) {
        let t = this.hasProp(
            e,
            this.validatorSet.backgroundProps,
            "background-color"
          )
            ? e["background-color"].evaluate(this.context)
            : null,
          i = this.hasProp(
            e,
            this.validatorSet.backgroundProps,
            "background-image"
          )
            ? e["background-image"].evaluate(this.context)
            : null;
        ((t && !M(t)) || (i && !M(i))) &&
          (this.transferPropsToRoot(e, this.validatorSet.backgroundProps),
          (this.rootBackgroundAssigned = !0));
      }
      if (!this.rootLayoutAssigned)
        for (let t = 0; t < qf.length; t++)
          if (this.hasProp(e, this.validatorSet.layoutProps, qf[t])) {
            this.transferPropsToRoot(e, this.validatorSet.layoutProps),
              (this.rootLayoutAssigned = !0);
            break;
          }
      if (!t) {
        let t = e["font-size"],
          n = !0;
        if (t && !M(t.value)) {
          let e = t.evaluate(this.context);
          if (e instanceof P) {
            let t = e.num;
            switch (e.unit) {
              case "em":
              case "rem":
                t *= this.context.initialFontSize;
                break;
              case "ex":
                t *= (this.context.initialFontSize * Y.ex) / Y.em;
                break;
              case "%":
                t *= this.context.initialFontSize / 100;
                break;
              case "lh":
              case "rlh":
                t *= (this.context.initialFontSize * Y.lh) / Y.em;
                break;
              default: {
                let i = Y[e.unit];
                i && (t *= i), (n = !1);
              }
            }
            (this.context.rootFontSize = t),
              (this.context.isRelativeRootFontSize = n);
          }
        }
        let r =
            null != (i = this.context.rootFontSize)
              ? i
              : this.context.initialFontSize,
          s = e["line-height"];
        if (s && !M(s.value)) {
          let e = s.evaluate(this.context);
          if (e instanceof nt) this.context.rootLineHeight = e.num * r;
          else if (e instanceof P) {
            let t = e.num;
            switch (e.unit) {
              case "em":
              case "rem":
                t *= r;
                break;
              case "ex":
                t *= (r * Y.ex) / Y.em;
                break;
              case "%":
                t *= r / 100;
                break;
              case "lh":
              case "rlh":
                t *= (this.context.initialFontSize * Y.lh) / Y.em;
                break;
              default: {
                let i = Y[e.unit];
                i && (t *= i);
              }
            }
            this.context.rootLineHeight = t;
          }
        } else
          this.context.rootLineHeight = (this.context.fontSize() * Y.lh) / Y.em;
      }
    }
    getTopContainerStyle() {
      let e = 0;
      for (
        ;
        !this.bodyReached &&
        ((e += 5e3), this.styleUntil(e, 0) != Number.POSITIVE_INFINITY);

      );
      return this.rootStyle;
    }
    getAttrStyle(e) {
      if (e.style instanceof CSSStyleDeclaration) {
        let t = e.getAttribute("style");
        if (t) return Sf(this.scope, this.validatorSet, this.xmldoc.url, t);
      }
      return {};
    }
    getReachedOffset() {
      return this.lastOffset;
    }
    replayFlowElementsFromOffset(e) {
      if (e >= this.lastOffset) return;
      let t = this.context,
        i = this.xmldoc.getElementOffset(this.root);
      if (e < i) {
        let e = this.getStyle(this.root, !1),
          n = he(e, "flow-into"),
          r = n ? n.evaluate(t, "flow-into").toString() : "body",
          s = this.encounteredFlowElement(r, e, this.root, i);
        this.boxStack.empty() && this.boxStack.push(e, i, !0, s);
      }
      let n = this.xmldoc.getNodeByOffset(e),
        r = this.xmldoc.getNodeOffset(n, 0, !1);
      if (!(r >= this.lastOffset))
        for (;;) {
          if (1 != n.nodeType) r += n.textContent.length;
          else {
            let e = n,
              i = this.getStyle(e, !1),
              s = i["flow-into"];
            if (s) {
              let n = s.evaluate(t, "flow-into").toString();
              this.encounteredFlowElement(n, i, e, r);
            }
            r++;
          }
          if (r >= this.lastOffset) break;
          let e = n.firstChild;
          if (null == e)
            for (; (e = n.nextSibling), !e; )
              if (((n = n.parentNode), n === this.root)) return;
          n = e;
        }
    }
    resetFlowChunkStream(e) {
      this.flowListener = e;
      for (let e = 0; e < this.flowChunks.length; e++)
        this.flowListener.encounteredFlowChunk(
          this.flowChunks[e],
          this.flows[this.flowChunks[e].flowName]
        );
    }
    styleUntilFlowIsReached(e) {
      this.flowToReach = e;
      let t = 0;
      for (
        ;
        null != this.flowToReach &&
        ((t += 5e3), this.styleUntil(t, 0) != Number.POSITIVE_INFINITY);

      );
    }
    styleUntilIdIsReached(e) {
      if (!e) return;
      this.idToReach = e;
      let t = 0;
      for (
        ;
        this.idToReach &&
        ((t += 5e3), this.styleUntil(t, 0) !== Number.POSITIVE_INFINITY);

      );
      this.idToReach = null;
    }
    encounteredFlowElement(e, t, i, n) {
      let r = 0,
        s = Number.POSITIVE_INFINITY,
        o = !1,
        a = !1,
        l = !1,
        h = t["flow-options"];
      if (h) {
        let e = dh(h.evaluate(this.context, "flow-options"));
        (o = !!e.exclusive), (a = !!e.static), (l = !!e.last);
      }
      let u = t["flow-linger"];
      u &&
        (s = Kl(
          u.evaluate(this.context, "flow-linger"),
          Number.POSITIVE_INFINITY
        ));
      let c = t["flow-priority"];
      c && (r = Kl(c.evaluate(this.context, "flow-priority"), 0));
      let d = this.breakBeforeValues[n] || null,
        p = this.flows[e];
      if (!p) {
        let t = this.boxStack.lastFlowName();
        p = this.flows[e] = new Yr(e, t);
      }
      let f = new qr(e, i, n, r, s, o, a, l, d);
      return (
        this.flowChunks.push(f),
        this.flowToReach == e && (this.flowToReach = null),
        this.flowListener && this.flowListener.encounteredFlowChunk(f, p),
        f
      );
    }
    registerForcedBreakOffset(e, t, i) {
      if (Ie(e)) {
        let e = this.flows[i].forcedBreakOffsets;
        (0 === e.length || e[e.length - 1] < t) && e.push(t);
      }
      let n = this.breakBeforeValues[t];
      this.breakBeforeValues[t] = Ve(n, e);
    }
    styleUntil(e, t) {
      let i,
        n = -1;
      if (
        e <= this.lastOffset &&
        ((i = this.offsetMap.slippedByFixed(e)),
        (n = i + t),
        n < this.offsetMap.getMaxSlipped())
      )
        return this.offsetMap.fixedBySlipped(n);
      if (null == this.last) return Number.POSITIVE_INFINITY;
      let r = this.context;
      for (;;) {
        let s = this.last.firstChild;
        if (null == s)
          for (;;) {
            if (1 == this.last.nodeType) {
              this.cascade.popElement(this.last),
                (this.primary = this.primaryStack.pop());
              let e = this.boxStack.pop(this.lastOffset),
                t = null;
              if (e.afterBox) {
                let i = e.afterBox.getBreakValue("before");
                this.registerForcedBreakOffset(
                  i,
                  e.afterBox.atBlockStart
                    ? this.boxStack.nearestBlockStartOffset(e)
                    : e.afterBox.offset,
                  e.flowName
                ),
                  (t = e.afterBox.getBreakValue("after"));
              }
              (t = Ve(t, e.getBreakValue("after"))),
                this.registerForcedBreakOffset(t, this.lastOffset, e.flowName);
            }
            if (((s = this.last.nextSibling), s)) break;
            if (((this.last = this.last.parentNode), this.last === this.root))
              return (
                (this.last = null),
                e < this.lastOffset &&
                (n < 0 && ((i = this.offsetMap.slippedByFixed(e)), (n = i + t)),
                n <= this.offsetMap.getMaxSlipped())
                  ? this.offsetMap.fixedBySlipped(n)
                  : Number.POSITIVE_INFINITY
              );
          }
        if (((this.last = s), 1 != this.last.nodeType))
          (this.lastOffset += this.last.textContent.length),
            this.boxStack.encounteredTextNode(this.last),
            this.primary
              ? this.offsetMap.addStuckRange(this.lastOffset)
              : this.offsetMap.addSlippedRange(this.lastOffset);
        else {
          let s = this.last,
            o = this.getAttrStyle(s);
          this.primaryStack.push(this.primary),
            this.cascade.pushElement(this, s, o, this.lastOffset);
          let a =
            s.getAttribute("id") ||
            s.getAttributeNS("http://www.w3.org/XML/1998/namespace", "id");
          a && a === this.idToReach && (this.idToReach = null),
            !this.bodyReached &&
              "body" == s.localName &&
              s.parentNode == this.root &&
              (this.postprocessTopStyle(o, !0), (this.bodyReached = !0));
          let l,
            h = o["flow-into"];
          if (h) {
            let e = h.evaluate(r, "flow-into").toString(),
              t = this.encounteredFlowElement(e, o, s, this.lastOffset);
            (this.primary = !!this.primaryFlows[e]),
              (l = this.boxStack.push(o, this.lastOffset, s === this.root, t));
          } else
            (l = this.boxStack.push(o, this.lastOffset, s === this.root)),
              s === this.xmldoc.body &&
                (l.breakBefore = Ve(l.flowChunk.breakBefore, l.breakBefore));
          let u = this.boxStack.nearestBlockStartOffset(l);
          if (0 === u) {
            let e = o.page,
              t = e && !M(e.value) && e.value.toString();
            t && "auto" !== t.toLowerCase() && (this.cascade.firstPageType = t);
          }
          if (
            (this.registerForcedBreakOffset(l.breakBefore, u, l.flowName),
            l.beforeBox)
          ) {
            let e = l.beforeBox.getBreakValue("after");
            this.registerForcedBreakOffset(
              e,
              l.beforeBox.atBlockStart ? u : l.offset,
              l.flowName
            );
          }
          if (
            (this.primary && l.displayValue() === b.none && (this.primary = !1),
            (this.styleMap[`e${this.lastOffset}`] = o),
            this.lastOffset++,
            this.primary
              ? this.offsetMap.addStuckRange(this.lastOffset)
              : this.offsetMap.addSlippedRange(this.lastOffset),
            this.bodyReached && 0 === u)
          )
            continue;
          if (
            e < this.lastOffset &&
            (n < 0 && ((i = this.offsetMap.slippedByFixed(e)), (n = i + t)),
            n <= this.offsetMap.getMaxSlipped())
          )
            return this.offsetMap.fixedBySlipped(n);
        }
      }
    }
    getStyle(e, t) {
      let i = this.xmldoc.getElementOffset(e),
        n = `e${i}`;
      return (
        t && (i = this.xmldoc.getNodeOffset(e, 0, !0)),
        this.lastOffset <= i && this.styleUntil(i, 0),
        this.styleMap[n]
      );
    }
    processContent(e, t, i) {}
  },
  qf = ["column-count", "column-width", "column-fill"],
  es = class {
    constructor(e) {
      (this.validator = e),
        p(this, "success", null),
        p(this, "failure", null),
        p(this, "code", 0);
    }
    isSpecial() {
      return 0 != this.code;
    }
    markAsStartGroup() {
      this.code = -1;
    }
    isStartGroup() {
      return -1 == this.code;
    }
    markAsEndGroup() {
      this.code = -2;
    }
    isEndGroup() {
      return -2 == this.code;
    }
    markAsStartAlternate(e) {
      this.code = 2 * e + 1;
    }
    isStartAlternate() {
      return this.code > 0 && this.code % 2 != 0;
    }
    markAsEndAlternate(e) {
      this.code = 2 * e + 2;
    }
    isEndAlternate() {
      return this.code > 0 && this.code % 2 == 0;
    }
    getAlternate() {
      return Math.floor((this.code - 1) / 2);
    }
  },
  Tn = class {
    constructor(e, t) {
      (this.where = e), (this.success = t), p(this, "what", -1);
    }
  },
  _s = class e {
    constructor() {
      p(this, "nodes", []),
        p(this, "connections", []),
        p(this, "match", []),
        p(this, "nomatch", []),
        p(this, "error", []),
        p(this, "emptyHead", !0);
    }
    connect(e, t) {
      for (let i = 0; i < e.length; i++) this.connections[e[i]].what = t;
      e.splice(0, e.length);
    }
    clone() {
      let t = new e();
      for (let e = 0; e < this.nodes.length; e++) {
        let i = this.nodes[e],
          n = new es(i.validator);
        (n.code = i.code), t.nodes.push(n);
      }
      for (let e = 0; e < this.connections.length; e++) {
        let i = this.connections[e],
          n = new Tn(i.where, i.success);
        (n.what = i.what), t.connections.push(n);
      }
      return (
        t.match.push(...this.match),
        t.nomatch.push(...this.nomatch),
        t.error.push(...this.error),
        t
      );
    }
    addSpecialToArr(e, t, i) {
      let n = this.nodes.length,
        r = new es(Kf);
      i >= 0
        ? t
          ? r.markAsStartAlternate(i)
          : r.markAsEndAlternate(i)
        : t
        ? r.markAsStartGroup()
        : r.markAsEndGroup(),
        this.nodes.push(r),
        this.connect(e, n);
      let s = new Tn(n, !0),
        o = new Tn(n, !1);
      e.push(this.connections.length),
        this.connections.push(o),
        e.push(this.connections.length),
        this.connections.push(s);
    }
    endSpecialGroup() {
      let e = [this.match, this.nomatch, this.error];
      for (let t = 0; t < e.length; t++) this.addSpecialToArr(e[t], !1, -1);
    }
    startSpecialGroup() {
      if (this.nodes.length) throw new Error("invalid call");
      this.addSpecialToArr(this.match, !0, -1);
    }
    endClause(e) {
      this.addSpecialToArr(this.match, !1, e);
    }
    startClause(e) {
      if (this.nodes.length) throw new Error("invalid call");
      let t = new es(Kf);
      t.markAsStartAlternate(e), this.nodes.push(t);
      let i = new Tn(0, !0),
        n = new Tn(0, !1);
      this.nomatch.push(this.connections.length),
        this.connections.push(n),
        this.match.push(this.connections.length),
        this.connections.push(i);
    }
    addPrimitive(e) {
      let t = this.nodes.length;
      this.nodes.push(new es(e));
      let i = new Tn(t, !0),
        n = new Tn(t, !1);
      this.connect(this.match, t),
        this.emptyHead
          ? (this.nomatch.push(this.connections.length), (this.emptyHead = !1))
          : this.error.push(this.connections.length),
        this.connections.push(n),
        this.match.push(this.connections.length),
        this.connections.push(i);
    }
    isSimple() {
      return 1 == this.nodes.length && !this.nodes[0].isSpecial();
    }
    isPrimitive() {
      return this.isSimple() && this.nodes[0].validator instanceof Ce;
    }
    addGroup(e, t) {
      if (0 == e.nodes.length) return;
      let i = this.nodes.length;
      if (4 == t && 1 == i && e.isPrimitive() && this.isPrimitive())
        return void (this.nodes[0].validator = this.nodes[0].validator.combine(
          e.nodes[0].validator
        ));
      for (let t = 0; t < e.nodes.length; t++) this.nodes.push(e.nodes[t]);
      4 == t
        ? ((this.emptyHead = !0), this.connect(this.nomatch, i))
        : this.connect(this.match, i);
      let n = this.connections.length;
      for (let t = 0; t < e.connections.length; t++) {
        let n = e.connections[t];
        (n.where += i), n.what >= 0 && (n.what += i), this.connections.push(n);
      }
      for (let t = 0; t < e.match.length; t++) this.match.push(e.match[t] + n);
      if ((3 == t && this.connect(this.match, i), 2 == t || 3 == t))
        for (let t = 0; t < e.nomatch.length; t++)
          this.match.push(e.nomatch[t] + n);
      else if (this.emptyHead) {
        for (let t = 0; t < e.nomatch.length; t++)
          this.nomatch.push(e.nomatch[t] + n);
        this.emptyHead = e.emptyHead;
      } else
        for (let t = 0; t < e.nomatch.length; t++)
          this.error.push(e.nomatch[t] + n);
      for (let t = 0; t < e.error.length; t++) this.error.push(e.error[t] + n);
      (e.nodes = null), (e.connections = null);
    }
    finish(e, t) {
      let i = this.nodes.length;
      this.nodes.push(e),
        this.nodes.push(t),
        this.connect(this.match, i),
        this.connect(this.nomatch, i + 1),
        this.connect(this.error, i + 1);
      for (let e of this.connections)
        e.success
          ? (this.nodes[e.where].success = this.nodes[e.what])
          : (this.nodes[e.where].failure = this.nodes[e.what]);
      for (let e = 0; e < i; e++)
        if (null == this.nodes[e].failure || null == this.nodes[e].success)
          throw new Error("Invalid validator state");
      return this.nodes[0];
    }
  },
  QC = 1,
  Qf = 2,
  Jf = 4,
  Ms = 8,
  Ia = 16,
  Od = 32,
  Va = 64,
  eg = 128,
  Ri = 256,
  Ii = 512,
  Dd = 1024,
  tg = 2048,
  ng = 4096,
  sg = 8192,
  Fa = class extends ct {
    constructor() {
      super();
    }
    validateForShorthand(e, t) {
      let i = e[t].visit(this);
      return i ? [i] : null;
    }
  },
  Ce = class e extends Fa {
    constructor(e, t, i) {
      super(), (this.allowed = e), (this.idents = t), (this.units = i);
    }
    visitEmpty(e) {
      return this.allowed & QC ? e : null;
    }
    visitSlash(e) {
      return this.allowed & tg ? e : null;
    }
    visitStr(e) {
      return this.allowed & Qf ? e : null;
    }
    visitIdent(e) {
      return (
        this.idents[e.name.toLowerCase()] ||
        (this.allowed & Jf ||
        (this.allowed & Va && CSS.supports("color", e.name))
          ? e
          : null)
      );
    }
    visitNumeric(e) {
      return 0 != e.num || this.allowed & Ii
        ? e.num < 0 && !(this.allowed & Ri)
          ? null
          : this.units[e.unit]
          ? e
          : null
        : "%" == e.unit && this.allowed & Dd
        ? e
        : null;
    }
    visitNum(e) {
      return 0 == e.num
        ? this.allowed & Ii
          ? e
          : null
        : e.num <= 0 && !(this.allowed & Ri)
        ? null
        : this.allowed & Ia
        ? e
        : null;
    }
    visitInt(e) {
      if (0 == e.num) return this.allowed & Ii ? e : null;
      if (e.num <= 0 && !(this.allowed & Ri)) return null;
      if (this.allowed & (Od | Ia)) return e;
      return this.idents[`${e.num}`] || null;
    }
    visitHexColor(e) {
      return this.allowed & Va &&
        /^([0-9A-F]{3,4}|([0-9A-F]{2}){3,4})$/i.test(e.hex)
        ? e
        : null;
    }
    visitURL(e) {
      return this.allowed & eg ? e : null;
    }
    visitURange(e) {
      return this.allowed & ng ? e : null;
    }
    visitSpaceList(e) {
      return null;
    }
    visitCommaList(e) {
      return null;
    }
    visitFunc(e) {
      return (this.allowed & Va && CSS.supports("color", e.toString())) ||
        (this.allowed & sg && CSS.supports("background-image", e.toString())) ||
        ("calc" === e.name && this.allowed & (Ms | Ia | Od | Ri | Ii | Dd))
        ? e
        : null;
    }
    visitExpr(e) {
      return 2046 & this.allowed ? e : null;
    }
    combine(t) {
      let i = {},
        n = {};
      for (let e in this.idents) i[e] = this.idents[e];
      for (let e in t.idents) i[e] = t.idents[e];
      for (let e in this.units) n[e] = this.units[e];
      for (let e in t.units) n[e] = t.units[e];
      return new e(this.allowed | t.allowed, i, n);
    }
  },
  j = {},
  Kf = new Ce(0, j, j),
  Vi = class extends Fa {
    constructor(e) {
      super(),
        p(this, "successTerminal"),
        p(this, "failureTerminal"),
        p(this, "first"),
        (this.successTerminal = new es(null)),
        (this.failureTerminal = new es(null)),
        (this.first = e.finish(this.successTerminal, this.failureTerminal));
    }
    validateList(e, t, i) {
      let n = t ? [] : e,
        r = this.first,
        s = i,
        o = null,
        a = null;
      for (; r !== this.successTerminal && r !== this.failureTerminal; ) {
        if (s >= e.length) {
          r = r.failure;
          continue;
        }
        let l = e[s],
          h = l;
        if (r.isSpecial()) {
          let e = !0;
          r.isStartGroup()
            ? (o ? o.push(a) : (o = [a]), (a = []))
            : r.isEndGroup()
            ? (a = o.length > 0 ? o.pop() : null)
            : r.isEndAlternate()
            ? (a[r.getAlternate()] = "taken")
            : (e = null == a[r.getAlternate()]),
            (r = e ? r.success : r.failure);
        } else {
          if (0 == s && !t && r.validator instanceof Us && this instanceof Us) {
            if (((h = new q(e).visit(r.validator)), h)) {
              (s = e.length), (r = r.success);
              continue;
            }
          } else if (
            0 == s &&
            !t &&
            r.validator instanceof Fi &&
            this instanceof Us
          ) {
            if (((h = new ge(e).visit(r.validator)), h)) {
              (s = e.length), (r = r.success);
              continue;
            }
          } else h = l.visit(r.validator);
          if (!h) {
            r = r.failure;
            continue;
          }
          if (h !== l && e === n) {
            n = [];
            for (let t = 0; t < s; t++) n[t] = e[t];
          }
          e !== n && (n[s - i] = h), s++, (r = r.success);
        }
      }
      return r === this.successTerminal && (t ? n.length > 0 : s == e.length)
        ? n
        : null;
    }
    validateSingle(e) {
      let t = null,
        i = this.first;
      for (; i !== this.successTerminal && i !== this.failureTerminal; )
        e
          ? i.isSpecial()
            ? (i = i.success)
            : ((t = e.visit(i.validator)),
              t ? ((e = null), (i = i.success)) : (i = i.failure))
          : (i = i.failure);
      return i === this.successTerminal ? t : null;
    }
    visitEmpty(e) {
      return this.validateSingle(e);
    }
    visitSlash(e) {
      return this.validateSingle(e);
    }
    visitStr(e) {
      return this.validateSingle(e);
    }
    visitIdent(e) {
      return this.validateSingle(e);
    }
    visitNumeric(e) {
      return this.validateSingle(e);
    }
    visitNum(e) {
      return this.validateSingle(e);
    }
    visitInt(e) {
      return this.validateSingle(e);
    }
    visitHexColor(e) {
      return this.validateSingle(e);
    }
    visitURL(e) {
      return this.validateSingle(e);
    }
    visitURange(e) {
      return this.validateSingle(e);
    }
    visitSpaceList(e) {
      return null;
    }
    visitCommaList(e) {
      return null;
    }
    visitFunc(e) {
      return this.validateSingle(e);
    }
    visitExpr(e) {
      return null;
    }
  },
  Us = class extends Vi {
    constructor(e) {
      super(e);
    }
    visitSpaceList(e) {
      let t = this.validateList(e.values, !1, 0);
      return t === e.values ? e : t ? new q(t) : null;
    }
    visitCommaList(e) {
      let t = this.first,
        i = !1;
      for (; t; ) {
        if (t.validator instanceof Fi) {
          i = !0;
          break;
        }
        t = t.failure;
      }
      if (i) {
        let t = this.validateList(e.values, !1, 0);
        return t === e.values ? e : t ? new ge(t) : null;
      }
      return null;
    }
    validateForShorthand(e, t) {
      return this.validateList(e, !0, t);
    }
  },
  Fi = class extends Vi {
    constructor(e) {
      super(e);
    }
    visitSpaceList(e) {
      return this.validateSingle(e);
    }
    visitCommaList(e) {
      let t = this.validateList(e.values, !1, 0);
      return t === e.values ? e : t ? new ge(t) : null;
    }
    validateForShorthand(e, t) {
      let i,
        n = this.first;
      for (; n !== this.failureTerminal; ) {
        if (((i = n.validator.validateForShorthand(e, t)), i)) return i;
        n = n.failure;
      }
      return null;
    }
  },
  Md = class extends Vi {
    constructor(e, t) {
      super(t), (this.name = e);
    }
    validateSingle(e) {
      return null;
    }
    visitFunc(e) {
      if (e.name.toLowerCase() != this.name) return null;
      let t = this.validateList(e.values, !1, 0);
      return t === e.values ? e : t ? new At(e.name, t) : null;
    }
  },
  Ba = class {
    tryParse(e, t, i) {
      return t;
    }
    success(e, t) {}
  },
  Oa = class extends Ba {
    constructor(e, t) {
      super(),
        (this.name = t),
        p(this, "validator"),
        (this.validator = e.validators[this.name]);
    }
    tryParse(e, t, i) {
      if (i.values[this.name]) return t;
      let n = this.validator.validateForShorthand(e, t);
      if (n) {
        let e = n.length,
          r = e > 1 ? new q(n) : n[0];
        return this.success(r, i), t + e;
      }
      return t;
    }
    success(e, t) {
      t.values[this.name] = e;
    }
  },
  _d = class extends Oa {
    constructor(e, t) {
      super(e, t[0]), (this.names = t);
    }
    success(e, t) {
      for (let i = 0; i < this.names.length; i++) t.values[this.names[i]] = e;
    }
  },
  Ud = class extends Ba {
    constructor(e, t) {
      super(), (this.nodes = e), (this.slash = t);
    }
    tryParse(e, t, i) {
      let n = t;
      if (this.slash) {
        if (e[t] != to) return n;
        if (++t == e.length) return n;
      }
      let r = this.nodes[0].tryParse(e, t, i);
      if (r == t) return n;
      t = r;
      for (
        let n = 1;
        n < this.nodes.length &&
        t < e.length &&
        ((r = this.nodes[n].tryParse(e, t, i)), r != t);
        n++
      )
        t = r;
      return t;
    }
  },
  Bi = class extends ct {
    constructor() {
      super(...arguments),
        p(this, "syntax", null),
        p(this, "propList", null),
        p(this, "error", !1),
        p(this, "values", {}),
        p(this, "validatorSet", null);
    }
    setOwner(e) {
      this.validatorSet = e;
    }
    syntaxNodeForProperty(e) {
      return new Oa(this.validatorSet, e);
    }
    clone() {
      let e = new this.constructor();
      return (
        (e.syntax = this.syntax),
        (e.propList = this.propList),
        (e.validatorSet = this.validatorSet),
        e
      );
    }
    init(e, t) {
      (this.syntax = e), (this.propList = t);
    }
    finish(e, t) {
      var i, n;
      if (!this.error) {
        for (let r of this.propList)
          t.simpleProperty(
            r,
            null !=
              (n =
                null != (i = this.values[r])
                  ? i
                  : this.validatorSet.defaultValues[r])
              ? n
              : b.initial,
            e
          );
        return !0;
      }
      return !1;
    }
    propagateDefaultingValue(e, t, i) {
      for (let n of this.propList) i.simpleProperty(n, e, t);
    }
    validateList(e) {
      return (this.error = !0), 0;
    }
    validateSingle(e) {
      return this.validateList([e]), null;
    }
    visitEmpty(e) {
      return this.validateSingle(e);
    }
    visitStr(e) {
      return this.validateSingle(e);
    }
    visitIdent(e) {
      return this.validateSingle(e);
    }
    visitNumeric(e) {
      return this.validateSingle(e);
    }
    visitNum(e) {
      return this.validateSingle(e);
    }
    visitInt(e) {
      return this.validateSingle(e);
    }
    visitHexColor(e) {
      return this.validateSingle(e);
    }
    visitURL(e) {
      return this.validateSingle(e);
    }
    visitSpaceList(e) {
      return this.validateList(e.values), null;
    }
    visitCommaList(e) {
      return (this.error = !0), null;
    }
    visitFunc(e) {
      return this.validateSingle(e);
    }
    visitExpr(e) {
      return this.validateSingle(e);
    }
  },
  ts = class extends Bi {
    constructor() {
      super();
    }
    validateList(e) {
      let t = 0,
        i = 0;
      for (; t < e.length; ) {
        let n = this.syntax[i].tryParse(e, t, this);
        if (n > t) (t = n), (i = 0);
        else if (++i == this.syntax.length) {
          this.error = !0;
          break;
        }
      }
      return t;
    }
  },
  Da = class extends Bi {
    constructor() {
      super();
    }
    validateList(e) {
      if (e.length > this.syntax.length || 0 == e.length)
        return (this.error = !0), 0;
      for (let t = 0; t < this.syntax.length; t++) {
        let i = t;
        for (; i >= e.length; ) i = 1 == i ? 0 : i - 2;
        if (this.syntax[t].tryParse(e, i, this) != i + 1)
          return (this.error = !0), 0;
      }
      return e.length;
    }
    createSyntaxNode() {
      return new _d(this.validatorSet, this.propList);
    }
  },
  Hd = class extends Bi {
    constructor() {
      super();
    }
    validateList(e) {
      let t = e.length;
      for (let i = 0; i < e.length; i++)
        if (e[i] === to) {
          t = i;
          break;
        }
      if (t > this.syntax.length || 0 == e.length) return (this.error = !0), 0;
      for (let i = 0; i < this.syntax.length; i++) {
        let n,
          r = i;
        for (; r >= t; ) r = 1 == r ? 0 : r - 2;
        if (t + 1 < e.length)
          for (n = t + i + 1; n >= e.length; ) n -= n == t + 2 ? 1 : 2;
        else n = r;
        let s = [e[r], e[n]];
        if (2 != this.syntax[i].tryParse(s, 0, this))
          return (this.error = !0), 0;
      }
      return e.length;
    }
  },
  zd = class extends ts {
    constructor() {
      super();
    }
    mergeIn(e, t) {
      var i, n;
      for (let r of this.propList) {
        let s =
            null !=
            (n = null != (i = t[r]) ? i : this.validatorSet.defaultValues[r])
              ? n
              : b.initial,
          o = e[r];
        o || ((o = []), (e[r] = o)), o.push(s);
      }
    }
    visitCommaList(e) {
      let t = {};
      for (let i = 0; i < e.values.length; i++)
        if (
          ((this.values = {}),
          e.values[i] instanceof ge
            ? (this.error = !0)
            : (e.values[i].visit(this),
              this.mergeIn(t, this.values),
              this.values["background-color"] &&
                i != e.values.length - 1 &&
                (this.error = !0)),
          this.error)
        )
          return null;
      this.values = {};
      for (let e in t)
        this.values[e] = "background-color" == e ? t[e].pop() : new ge(t[e]);
      return null;
    }
  },
  Gd = class extends ts {
    constructor() {
      super();
    }
    init(e, t) {
      super.init(e, t),
        this.propList.push(
          "font-family",
          "line-height",
          "font-size",
          "font-stretch",
          "font-variant-ligatures",
          "font-variant-caps",
          "font-variant-numeric",
          "font-variant-east-asian"
        );
    }
    validateList(e) {
      let t = super.validateList(e),
        i = this.values["font-variant_css2"];
      i &&
        (delete this.values["font-variant_css2"],
        (this.values["font-variant-caps"] = i));
      let n = this.values["font-stretch_css3"];
      if (
        (n &&
          (delete this.values["font-stretch_css3"],
          (this.values["font-stretch"] = n)),
        t + 2 > e.length)
      )
        return (this.error = !0), t;
      this.error = !1;
      let r = this.validatorSet.validators;
      if (!e[t].visit(r["font-size"])) return (this.error = !0), t;
      if (((this.values["font-size"] = e[t++]), e[t] === to)) {
        if ((t++, t + 2 > e.length)) return (this.error = !0), t;
        if (!e[t].visit(r["line-height"])) return (this.error = !0), t;
        this.values["line-height"] = e[t++];
      }
      let s = t == e.length - 1 ? e[t] : new q(e.slice(t, e.length));
      return s.visit(r["font-family"])
        ? ((this.values["font-family"] = s), e.length)
        : ((this.error = !0), t);
    }
    visitCommaList(e) {
      if ((e.values[0].visit(this), this.error)) return null;
      let t = [this.values["font-family"]];
      for (let i = 1; i < e.values.length; i++) t.push(e.values[i]);
      let i = new ge(t);
      return (
        i.visit(this.validatorSet.validators["font-family"])
          ? (this.values["font-family"] = i)
          : (this.error = !0),
        null
      );
    }
    visitIdent(e) {
      let t = this.validatorSet.systemFonts[e.name];
      if (t) for (let e in t) this.values[e] = t[e];
      else this.error = !0;
      return null;
    }
  },
  Wd = class extends ts {
    validateList(e) {
      if (1 === e.length && e[0] instanceof be)
        switch (e[0].name.toLowerCase()) {
          case "normal":
            e = [
              this.validatorSet.defaultValues["text-autospace"],
              this.validatorSet.defaultValues["text-spacing-trim"],
            ];
            break;
          case "auto":
            e = [b.auto, b.auto];
            break;
          case "none":
            e = [L("no-autospace"), L("space-all")];
        }
      return super.validateList(e);
    }
  },
  JC = [
    "unicode-bidi",
    "direction",
    "margin-block-start",
    "margin-block-end",
    "margin-inline-start",
    "margin-inline-end",
    "padding-block-start",
    "padding-block-end",
    "padding-inline-start",
    "padding-inline-end",
    "border-block-start-color",
    "border-block-end-color",
    "border-inline-start-color",
    "border-inline-end-color",
    "border-block-start-style",
    "border-block-end-style",
    "border-inline-start-style",
    "border-inline-end-style",
    "border-block-start-width",
    "border-block-end-width",
    "border-inline-start-width",
    "border-inline-end-width",
    "block-start",
    "block-end",
    "inline-start",
    "inline-end",
    "block-size",
    "inline-size",
    "max-block-size",
    "max-inline-size",
    "min-block-size",
    "min-inline-size",
    "behavior",
    "bleed",
    "conflicting-partitions",
    "crop-offset",
    "crop-marks-line-color",
    "enabled",
    "flow-consume",
    "flow-from",
    "flow-into",
    "flow-linger",
    "flow-options",
    "flow-priority",
    "font-display",
    "font-size-adjust",
    "font-stretch_css3",
    "font-variant_css2",
    "glyph-orientation-vertical",
    "marks",
    "min-page-height",
    "min-page-width",
    "repeat-on-break",
    "required",
    "required-partitions",
    "ruby-align",
    "shape-inside",
    "snap-height",
    "snap-width",
    "template",
    "text-decoration-skip",
    "text-justify",
    "text-zoom",
    "unicode-range",
    "utilization",
    "wrap-flow",
  ],
  $d = class extends ts {
    constructor() {
      super();
    }
    init(e, t) {
      super.init(e, t);
      for (let e in this.validatorSet.validators)
        JC.includes(e) || this.propList.push(e);
    }
    validateList(e) {
      return (this.error = !0), 0;
    }
  },
  Zf = {
    SIMPLE: ts,
    INSETS: Da,
    INSETS_SLASH: Hd,
    COMMA: zd,
    FONT: Gd,
    TEXT_SPACING: Wd,
    ALL: $d,
  },
  Xd = class {
    constructor() {
      p(this, "validators", {}),
        p(this, "prefixes", {}),
        p(this, "defaultValues", {}),
        p(this, "namedValidators", {}),
        p(this, "systemFonts", {}),
        p(this, "shorthands", {}),
        p(this, "layoutProps", {}),
        p(this, "backgroundProps", {});
    }
    addReplacement(e, t) {
      let i;
      if (3 == t.type) i = new P(t.num, t.text);
      else if (7 == t.type) i = new eo(t.text);
      else {
        if (1 != t.type) throw new Error("unexpected replacement");
        i = L(t.text);
      }
      if (e.isPrimitive()) {
        let t = e.nodes[0].validator.idents;
        for (let e in t) t[e] = i;
        return e;
      }
      throw new Error("unexpected replacement");
    }
    newGroup(e, t) {
      let i = new _s();
      if ("||" == e) {
        for (let e = 0; e < t.length; e++) {
          let n = new _s();
          n.startClause(e),
            n.addGroup(t[e], 1),
            n.endClause(e),
            i.addGroup(n, 0 == e ? 1 : 4);
        }
        let e = new _s();
        return e.startSpecialGroup(), e.addGroup(i, 3), e.endSpecialGroup(), e;
      }
      {
        let n;
        switch (e) {
          case " ":
            n = 1;
            break;
          case "|":
          case "||":
            n = 4;
            break;
          default:
            throw new Error("unexpected op");
        }
        for (let e = 0; e < t.length; e++) i.addGroup(t[e], 0 == e ? 1 : n);
        return i;
      }
    }
    addCounts(e, t, i) {
      let n = new _s();
      for (let i = 0; i < t; i++) n.addGroup(e.clone(), 1);
      if (i == Number.POSITIVE_INFINITY) n.addGroup(e, 3);
      else for (let r = t; r < i; r++) n.addGroup(e.clone(), 2);
      return n;
    }
    primitive(e) {
      let t = new _s();
      return t.addPrimitive(e), t;
    }
    newFunc(e, t) {
      let i;
      switch (e) {
        case "COMMA":
          i = new Fi(t);
          break;
        case "SPACE":
          i = new Us(t);
          break;
        default:
          i = new Md(e.toLowerCase(), t);
      }
      return this.primitive(i);
    }
    initBuiltInValidators() {
      (this.namedValidators.COLOR = this.primitive(new Ce(Va, j, j))),
        (this.namedValidators.IMAGE_FUNCTION = this.primitive(
          new Ce(sg, j, j)
        )),
        (this.namedValidators.POS_INT = this.primitive(new Ce(Od, j, j))),
        (this.namedValidators.POS_NUM = this.primitive(new Ce(Ia, j, j))),
        (this.namedValidators.POS_PERCENTAGE = this.primitive(
          new Ce(Ms, j, { "%": O })
        )),
        (this.namedValidators.NEGATIVE = this.primitive(new Ce(Ri, j, j))),
        (this.namedValidators.ZERO = this.primitive(new Ce(Ii, j, j))),
        (this.namedValidators.ZERO_PERCENTAGE = this.primitive(
          new Ce(Dd, j, j)
        )),
        (this.namedValidators.POS_LENGTH = this.primitive(
          new Ce(Ms, j, {
            em: O,
            ex: O,
            ch: O,
            rem: O,
            lh: O,
            rlh: O,
            vw: O,
            vh: O,
            vi: O,
            vb: O,
            vmin: O,
            vmax: O,
            pvw: O,
            pvh: O,
            pvi: O,
            pvb: O,
            pvmin: O,
            pvmax: O,
            cm: O,
            mm: O,
            in: O,
            px: O,
            pt: O,
            pc: O,
            q: O,
          })
        )),
        (this.namedValidators.POS_ANGLE = this.primitive(
          new Ce(Ms, j, { deg: O, grad: O, rad: O, turn: O })
        )),
        (this.namedValidators.POS_TIME = this.primitive(
          new Ce(Ms, j, { s: O, ms: O })
        )),
        (this.namedValidators.FREQUENCY = this.primitive(
          new Ce(Ms, j, { Hz: O, kHz: O })
        )),
        (this.namedValidators.RESOLUTION = this.primitive(
          new Ce(Ms, j, { dpi: O, dpcm: O, dppx: O })
        )),
        (this.namedValidators.URI = this.primitive(new Ce(eg, j, j))),
        (this.namedValidators.URANGE = this.primitive(new Ce(ng, j, j))),
        (this.namedValidators.IDENT = this.primitive(new Ce(Jf, j, j))),
        (this.namedValidators.STRING = this.primitive(new Ce(Qf, j, j))),
        (this.namedValidators.SLASH = this.primitive(new Ce(tg, j, j)));
      let e = { "font-family": L("sans-serif") };
      (this.systemFonts.caption = e),
        (this.systemFonts.icon = e),
        (this.systemFonts.menu = e),
        (this.systemFonts["message-box"] = e),
        (this.systemFonts["small-caption"] = e),
        (this.systemFonts["status-bar"] = e);
    }
    isBuiltIn(e) {
      return !!e.match(/^[A-Z_0-9]+$/);
    }
    readNameAndPrefixes(e, t) {
      let i = e.token();
      if (0 == i.type) return null;
      let n = { "": !0 };
      if (14 == i.type) {
        do {
          if ((e.consume(), (i = e.token()), 1 != i.type))
            throw new Error("Prefix name expected");
          (n[i.text] = !0), e.consume(), (i = e.token());
        } while (16 == i.type);
        if (15 != i.type) throw new Error("']' expected");
        e.consume(), (i = e.token());
      }
      if (1 != i.type) throw new Error("Property name expected");
      if (2 == t ? "SHORTHANDS" == i.text : "DEFAULTS" == i.text)
        return e.consume(), null;
      let r = i.text;
      if ((e.consume(), 2 != t)) {
        if (39 != e.token().type) throw new Error("'=' expected");
        this.isBuiltIn(r) || (this.prefixes[r] = n);
      } else if (18 != e.token().type) throw new Error("':' expected");
      return r;
    }
    parseValidators(e) {
      for (;;) {
        let t = this.readNameAndPrefixes(e, 1);
        if (!t) return;
        let i,
          n = [],
          r = [],
          s = "",
          o = !0,
          a = () => {
            if (0 == n.length) throw new Error("No values");
            return 1 == n.length ? n[0] : this.newGroup(s, n);
          },
          l = (e) => {
            if (o) throw new Error(`'${e}': unexpected`);
            if (s && s != e)
              throw new Error(`mixed operators: '${e}' and '${s}'`);
            (s = e), (o = !0);
          },
          h = null;
        for (; !h; ) {
          e.consume();
          let t = e.token();
          switch (t.type) {
            case 1:
              if ((o || l(" "), this.isBuiltIn(t.text))) {
                let e = this.namedValidators[t.text];
                if (!e) throw new Error(`'${t.text}' unexpected`);
                n.push(e.clone());
              } else {
                let e = {};
                (e[t.text.toLowerCase()] = L(t.text)),
                  n.push(this.primitive(new Ce(0, e, j)));
              }
              o = !1;
              break;
            case 5: {
              let e = {};
              (e[`${t.num}`] = new ut(t.num)),
                n.push(this.primitive(new Ce(0, e, j))),
                (o = !1);
              break;
            }
            case 34:
              l("|");
              break;
            case 25:
              l("||");
              break;
            case 14:
              o || l(" "),
                r.push({ vals: n, op: s, b: "[" }),
                (s = ""),
                (n = []),
                (o = !0);
              break;
            case 6:
              o || l(" "),
                r.push({ vals: n, op: s, b: "(", fn: t.text }),
                (s = ""),
                (n = []),
                (o = !0);
              break;
            case 15: {
              i = a();
              let e = r.pop();
              if ("[" != e.b) throw new Error("']' unexpected");
              (n = e.vals), n.push(i), (s = e.op), (o = !1);
              break;
            }
            case 11: {
              i = a();
              let e = r.pop();
              if ("(" != e.b) throw new Error("')' unexpected");
              (n = e.vals), n.push(this.newFunc(e.fn, i)), (s = e.op), (o = !1);
              break;
            }
            case 18:
              if (o) throw new Error("':' unexpected");
              e.consume(), n.push(this.addReplacement(n.pop(), e.token()));
              break;
            case 22:
              if (o) throw new Error("'?' unexpected");
              n.push(this.addCounts(n.pop(), 0, 1));
              break;
            case 36:
              if (o) throw new Error("'*' unexpected");
              n.push(this.addCounts(n.pop(), 0, Number.POSITIVE_INFINITY));
              break;
            case 23:
              if (o) throw new Error("'+' unexpected");
              n.push(this.addCounts(n.pop(), 1, Number.POSITIVE_INFINITY));
              break;
            case 12: {
              if ((e.consume(), (t = e.token()), 5 != t.type))
                throw new Error("<int> expected");
              let i = t.num,
                r = i;
              if ((e.consume(), (t = e.token()), 16 == t.type)) {
                if ((e.consume(), (t = e.token()), 5 != t.type))
                  throw new Error("<int> expected");
                (r = t.num), e.consume(), (t = e.token());
              }
              if (13 != t.type) throw new Error("'}' expected");
              n.push(this.addCounts(n.pop(), i, r));
              break;
            }
            case 17:
              if (((h = a()), r.length > 0))
                throw new Error(`unclosed '${r.pop().b}'`);
              break;
            default:
              throw new Error("unexpected token");
          }
        }
        e.consume(),
          this.isBuiltIn(t)
            ? (this.namedValidators[t] = h)
            : h.isSimple()
            ? (this.validators[t] = h.nodes[0].validator)
            : (this.validators[t] = new Us(h));
      }
    }
    parseDefaults(e) {
      for (;;) {
        let t = this.readNameAndPrefixes(e, 2);
        if (!t) return;
        let i = [];
        for (;;) {
          e.consume();
          let t = e.token();
          if (17 == t.type) {
            e.consume();
            break;
          }
          switch (t.type) {
            case 1:
              i.push(L(t.text));
              break;
            case 4:
              i.push(new nt(t.num));
              break;
            case 5:
              i.push(new ut(t.num));
              break;
            case 3:
              i.push(new P(t.num, t.text));
              break;
            default:
              throw new Error("unexpected token");
          }
        }
        this.defaultValues[t] = i.length > 1 ? new q(i) : i[0];
      }
    }
    parseShorthands(e) {
      for (;;) {
        let t = this.readNameAndPrefixes(e, 3);
        if (!t) return;
        let i,
          n = e.nthToken(1);
        1 == n.type && Zf[n.text]
          ? ((i = new Zf[n.text]()), e.consume())
          : (i = new ts()),
          i.setOwner(this);
        let r = !1,
          s = [],
          o = !1,
          a = [],
          l = [];
        for (; !r; )
          switch ((e.consume(), (n = e.token()), n.type)) {
            case 1:
              if (this.validators[n.text])
                s.push(i.syntaxNodeForProperty(n.text)),
                  n.text.includes("_") || l.push(n.text);
              else {
                if (!(this.shorthands[n.text] instanceof Da))
                  throw new Error(
                    `'${n.text}' is neither a simple property nor an inset shorthand`
                  );
                {
                  let e = this.shorthands[n.text];
                  s.push(e.createSyntaxNode()), l.push(...e.propList);
                }
              }
              break;
            case 19:
              if (s.length > 0 || o) throw new Error("unexpected slash");
              o = !0;
              break;
            case 14:
              a.push({ slash: o, syntax: s }), (s = []), (o = !1);
              break;
            case 15: {
              let e = new Ud(s, o),
                t = a.pop();
              (s = t.syntax), (o = t.slash), s.push(e);
              break;
            }
            case 17:
              (r = !0), e.consume();
              break;
            default:
              throw new Error("unexpected token");
          }
        i.init(s, l), (this.shorthands[t] = i);
      }
    }
    parse(e) {
      let t = new De(e, null);
      this.parseValidators(t),
        this.parseDefaults(t),
        this.parseShorthands(t),
        (this.backgroundProps = this.makePropSet(["background"])),
        (this.layoutProps = this.makePropSet([
          "margin",
          "border",
          "padding",
          "columns",
          "column-gap",
          "column-rule",
          "column-fill",
        ]));
    }
    makePropSet(e) {
      var t;
      let i = {};
      for (let n of e) {
        let e = this.shorthands[n],
          r = e ? e.propList : [n];
        for (let e of r)
          i[e] = null != (t = this.defaultValues[e]) ? t : b.initial;
      }
      return i;
    }
    validatePropertyAndHandleShorthand(e, t, i, n) {
      if (bt(e) || "font-face" === n.ruleType || eb(t))
        return void n.simpleProperty(e, t, i);
      let r = "",
        s = e,
        o = (e = e.toLowerCase()).match(/^-([a-z]+)-([-a-z0-9]+)$/);
      o && ((r = o[1]), (e = o[2]));
      let a = this.prefixes[e];
      if (!a || !a[r])
        return void (CSS.supports(s, t.toString())
          ? n.simpleProperty(s, t, i)
          : (r && !Al.includes(`-${r}-`)) || n.unknownProperty(s, t));
      let l = this.validators[e];
      if (l) {
        let o = M(t) || t.isExpr() ? t : t.visit(l);
        o
          ? n.simpleProperty(e, o, i)
          : !r && CSS.supports(e, t.toString())
          ? n.simpleProperty(e, t, i)
          : n.invalidPropertyValue(s, t);
      } else {
        let r = this.shorthands[e].clone();
        M(t)
          ? r.propagateDefaultingValue(t, i, n)
          : (t.visit(r), r.finish(i, n) || n.invalidPropertyValue(s, t));
      }
    }
  };
function Yd() {
  let e = new Xd();
  return e.initBuiltInValidators(), e.parse(bc), e;
}
var jd = class extends ct {
  constructor() {
    super(...arguments), p(this, "varFound", !1);
  }
  visitFunc(e) {
    return (
      "var" === e.name
        ? (this.varFound = !0)
        : this.varFound || this.visitValues(e.values),
      null
    );
  }
};
function eb(e) {
  let t = new jd();
  return e.visit(t), t.varFound;
}
var ry = `OTTO${new Date().valueOf()}`;
function qd(e) {
  return Object.keys(e)
    .filter((e) => !["src", "font-family", "font-display"].includes(e))
    .sort();
}
function nb(e) {
  let t = new $e();
  for (let i of qd(e)) t.append(`${i}:${e[i].toString()};`);
  return t.toString();
}
function ig(e, t) {
  let i = {};
  for (let n in e) i[n] = he(e, n).evaluate(t, n);
  return i;
}
var Oi = class {
    constructor(e) {
      (this.properties = e),
        p(this, "fontTraitKey"),
        p(this, "src"),
        p(this, "blobURLs", []),
        p(this, "blobs", []),
        p(this, "family"),
        (this.fontTraitKey = nb(this.properties)),
        (this.src = this.properties.src
          ? this.properties.src.toString()
          : null);
      let t = this.properties["font-family"];
      this.family = t ? t.stringValue() : null;
    }
    traitsEqual(e) {
      return this.fontTraitKey == e.fontTraitKey;
    }
    makeAtRule(e, t) {
      let i = new $e();
      i.append("@font-face {\n  font-family: "),
        i.append(this.family),
        i.append(";\n  ");
      for (let e of qd(this.properties))
        i.append(e),
          i.append(": "),
          this.properties[e].appendTo(i, !0),
          i.append(";\n  ");
      if (t) {
        i.append('src: url("');
        let e = xh(t);
        i.append(e), this.blobURLs.push(e), this.blobs.push(t), i.append('")');
      } else i.append("src: "), i.append(e);
      return i.append(";\n}\n"), i.toString();
    }
  },
  Ma = class {
    constructor(e) {
      (this.deobfuscator = e), p(this, "familyMap", {});
    }
    registerFamily(e, t) {
      let i = e.family,
        n = this.familyMap[i],
        r = t.family;
      if (n) {
        if (n != r) throw new Error(`E_FONT_FAMILY_INCONSISTENT ${e.family}`);
      } else this.familyMap[i] = r;
    }
    filterFontFamily(e) {
      if (e instanceof ge) {
        let t = e.values,
          i = [];
        for (let e of t) {
          let t = this.familyMap[e.stringValue()];
          t && i.push(L(t)), i.push(e);
        }
        return new ge(i);
      }
      {
        let t = this.familyMap[e.stringValue()];
        return t ? new ge([L(t), e]) : e;
      }
    }
  },
  _a = class {
    constructor(e, t, i) {
      (this.head = e),
        (this.body = t),
        p(this, "srcURLMap", {}),
        p(this, "familyPrefix"),
        p(this, "familyCounter", 0),
        (this.familyPrefix = i || "Fnt_");
    }
    getViewFontFamily(e, t) {
      let i = e.family,
        n = t.familyMap[i];
      return (
        n ||
        ((n = this.familyPrefix + ++this.familyCounter),
        (t.familyMap[i] = n),
        n)
      );
    }
    initFont(e, t, i) {
      let n = A("initFont"),
        r = e.src,
        s = {};
      for (let t of qd(e.properties)) s[t] = e.properties[t];
      let o = this.getViewFontFamily(e, i);
      s["font-family"] = L(o);
      let a = new Oi(s),
        l = this.head.ownerDocument.createElement("style");
      return (
        (l.textContent = a.makeAtRule(r, t)),
        this.head.appendChild(l),
        V.debug("Load font:", r),
        n.finish(a),
        n.result()
      );
    }
    loadFont(e, t) {
      let i = e.src,
        n = e.family + ";" + i,
        r = this.srcURLMap[n];
      return (
        r
          ? r.piggyback((n) => {
              let r = n;
              r.traitsEqual(e)
                ? (t.registerFamily(e, r),
                  V.debug("Found already-loaded font:", i))
                : V.warn("E_FONT_FACE_INCOMPATIBLE", e.src);
            })
          : ((r = new tn(() => {
              let n = A("loadFont"),
                r = i.replace(/^url\("([^"]+)"\).*$/, "$1"),
                s = t.deobfuscator ? t.deobfuscator(r) : null;
              return (
                s
                  ? bs(r, "blob").then((i) => {
                      i.responseBlob
                        ? s(i.responseBlob).then((i) => {
                            this.initFont(e, i, t).thenFinish(n);
                          })
                        : n.finish(null);
                    })
                  : this.initFont(e, null, t).thenFinish(n),
                n.result()
              );
            }, `loadFont ${i}`)),
            (this.srcURLMap[n] = r),
            r.start()),
        r
      );
    }
    findOrLoadFonts(e, t) {
      let i = [];
      for (let n of e)
        n.src && n.family
          ? i.push(this.loadFont(n, t))
          : V.warn("E_FONT_FACE_INVALID");
      return Bn(i).thenAsync(() => this.waitFontLoading());
    }
    waitFontLoading() {
      let e = this.head.ownerDocument.fonts,
        t = 0;
      if (
        (e.forEach((e) => {
          "unloaded" === e.status && (t++, e.load().catch((e) => {}));
        }),
        0 === t)
      )
        return T(!0);
      let i = A("waitFontLoading");
      return (
        i
          .loop(() =>
            i
              .sleep(20)
              .thenAsync(() => ("loading" === e.status ? T(!0) : T(!1)))
          )
          .thenFinish(i),
        i.result()
      );
    }
  },
  sb = 1,
  Ro = class {
    constructor(e, t, i, n, r) {
      (this.name = t),
        (this.pseudoName = i),
        (this.classes = n),
        (this.parent = r),
        p(this, "specified", {}),
        p(this, "children", []),
        p(this, "pageMaster", null),
        p(this, "index", 0),
        p(this, "key"),
        p(this, "_scope"),
        (this._scope = e),
        (this.key = "p" + sb++),
        r && ((this.index = r.children.length), r.children.push(this));
    }
    get scope() {
      return this._scope;
    }
    createInstance(e) {
      throw new Error("E_UNEXPECTED_CALL");
    }
    clone(e) {
      throw new Error("E_UNEXPECTED_CALL");
    }
    copySpecified(e) {
      let t = this.specified,
        i = e.specified;
      for (let e in t)
        Object.prototype.hasOwnProperty.call(t, e) && (i[e] = t[e]);
    }
    cloneChildren(e) {
      for (let t = 0; t < this.children.length; t++)
        this.children[t].clone({ parent: e });
    }
  },
  Ua = class extends Ro {
    constructor(e) {
      super(e, null, null, [], null),
        (this.specified.width = new _(Vn, 0)),
        (this.specified.height = new _(Fn, 0));
    }
  },
  Ha = class extends us {
    constructor(e, t) {
      super(e, function (e, t) {
        let n = e.match(/^([^.]+)\.([^.]+)$/);
        if (n) {
          let e = i.pageMaster.keyMap[n[1]];
          if (e) {
            let i = this.lookupInstance(e);
            if (i) return t ? i.resolveFunc(n[2]) : i.resolveName(n[2]);
          }
        }
        return null;
      }),
        (this.pageMaster = t);
      let i = this;
    }
  },
  Io = class e extends Ro {
    constructor(e, t, i, n, r, s, o) {
      super(e, t, i, n, r),
        (this.condition = s),
        (this.specificity = o),
        p(this, "keyMap", {}),
        e instanceof Ha || (this._scope = new Ha(e, this)),
        (this.pageMaster = this),
        (this.specified.width = new _(Vn, 0)),
        (this.specified.height = new _(Fn, 0)),
        (this.specified["wrap-flow"] = new _(b.auto, 0)),
        (this.specified.position = new _(b.relative, 0)),
        (this.specified.overflow = new _(b.visible, 0));
    }
    createInstance(e) {
      return new Hs(e, this);
    }
    clone(t) {
      let i = new e(
        this.scope,
        this.name,
        t.pseudoName || this.pseudoName,
        this.classes,
        this.parent,
        this.condition,
        this.specificity
      );
      return this.copySpecified(i), this.cloneChildren(i), i;
    }
    resetScope() {
      this.scope.pageMaster = this;
    }
  },
  za = class e extends Ro {
    constructor(e, t, i, n, r) {
      super(e, t, i, n, r),
        (this.pageMaster = r.pageMaster),
        t && (this.pageMaster.keyMap[t] = this.key),
        (this.specified["wrap-flow"] = new _(b.auto, 0));
    }
    createInstance(e) {
      return new Kd(e, this);
    }
    clone(t) {
      let i = new e(
        t.parent.scope,
        this.name,
        this.pseudoName,
        this.classes,
        t.parent
      );
      return this.copySpecified(i), this.cloneChildren(i), i;
    }
  },
  ns = class e extends Ro {
    constructor(e, t, i, n, r) {
      super(e, t, i, n, r),
        (this.pageMaster = r.pageMaster),
        t && (this.pageMaster.keyMap[t] = this.key);
    }
    createInstance(e) {
      return new cn(e, this);
    }
    clone(t) {
      let i = new e(
        t.parent.scope,
        this.name,
        this.pseudoName,
        this.classes,
        t.parent
      );
      return this.copySpecified(i), this.cloneChildren(i), i;
    }
  };
function ob(e, t, i) {
  return !t || M(t) ? new ie(e, i) : t.toExpr(e, e.zero);
}
function te(e, t, i) {
  return !t || t === b.auto || M(t) ? null : t.toExpr(e, i);
}
function ib(e, t, i) {
  return !t || t === b.normal || M(t) ? null : t.toExpr(e, i);
}
function ot(e, t, i) {
  return !t || t === b.auto || M(t) ? e.zero : t.toExpr(e, i);
}
function Qd(e, t, i) {
  return !t || M(t) ? e.zero : t === b.auto ? null : t.toExpr(e, i);
}
function $t(e, t, i, n) {
  return !t || i === b.none || M(t) ? e.zero : t.toExpr(e, n);
}
function pg(e, t, i) {
  return !t || M(t)
    ? i
    : t === b._true
    ? e._true
    : t === b._false
    ? e._false
    : t.toExpr(e, e.zero);
}
var Vo = class {
    constructor(e, t) {
      (this.parentInstance = e),
        (this.pageBox = t),
        p(this, "cascaded", {}),
        p(this, "style", {}),
        p(this, "autoWidth", null),
        p(this, "autoHeight", null),
        p(this, "children", []),
        p(this, "isAutoWidth", !1),
        p(this, "isAutoHeight", !1),
        p(this, "isTopDependentOnAutoHeight", !1),
        p(this, "isRightDependentOnAutoWidth", !1),
        p(this, "calculatedWidth", 0),
        p(this, "calculatedHeight", 0),
        p(this, "pageMasterInstance", null),
        p(this, "namedValues", {}),
        p(this, "namedFuncs", {}),
        p(this, "vertical", !1),
        p(this, "rtl", !1),
        p(this, "suppressEmptyBoxGeneration", !1),
        p(this, "borderBoxSizing", !1),
        e && e.children.push(this);
    }
    reset() {
      (this.calculatedWidth = 0), (this.calculatedHeight = 0);
    }
    addNamedValues(e, t) {
      let i = this.resolveName(e),
        n = this.resolveName(t);
      if (!i || !n) throw new Error("E_INTERNAL");
      return H(this.pageBox.scope, i, n);
    }
    resolveName(e) {
      let t = this.namedValues[e];
      if (t) return t;
      let i = this.style[e];
      switch (
        (i && (t = i.toExpr(this.pageBox.scope, this.pageBox.scope.zero)), e)
      ) {
        case "margin-left-edge":
          t = this.resolveName("left");
          break;
        case "margin-top-edge":
          t = this.resolveName("top");
          break;
        case "margin-right-edge":
          t = this.addNamedValues("border-right-edge", "margin-right");
          break;
        case "margin-bottom-edge":
          t = this.addNamedValues("border-bottom-edge", "margin-bottom");
          break;
        case "border-left-edge":
          t = this.addNamedValues("margin-left-edge", "margin-left");
          break;
        case "border-top-edge":
          t = this.addNamedValues("margin-top-edge", "margin-top");
          break;
        case "border-right-edge":
          t = this.addNamedValues("padding-right-edge", "border-right-width");
          break;
        case "border-bottom-edge":
          t = this.addNamedValues("padding-bottom-edge", "border-bottom-width");
          break;
        case "padding-left-edge":
          t = this.addNamedValues("border-left-edge", "border-left-width");
          break;
        case "padding-top-edge":
          t = this.addNamedValues("border-top-edge", "border-top-width");
          break;
        case "padding-right-edge":
          t = this.addNamedValues("right-edge", "padding-right");
          break;
        case "padding-bottom-edge":
          t = this.addNamedValues("bottom-edge", "padding-bottom");
          break;
        case "left-edge":
          t = this.addNamedValues("padding-left-edge", "padding-left");
          break;
        case "top-edge":
          t = this.addNamedValues("padding-top-edge", "padding-top");
          break;
        case "right-edge":
          t = this.addNamedValues("left-edge", "width");
          break;
        case "bottom-edge":
          t = this.addNamedValues("top-edge", "height");
      }
      if (!t) {
        let i;
        if ("extent" == e) i = this.vertical ? "width" : "height";
        else if ("measure" == e) i = this.vertical ? "height" : "width";
        else {
          let t = this.vertical ? Wu : $u;
          i = e;
          for (let e in t) i = i.replace(e, t[e]);
        }
        i != e && (t = this.resolveName(i));
      }
      return t && (this.namedValues[e] = t), t;
    }
    resolveFunc(e) {
      let t = this.namedFuncs[e];
      if (t) return t;
      switch (e) {
        case "columns": {
          let e = this.pageBox.scope,
            i = new Qs(e, 0),
            n = this.resolveName("column-count"),
            r = this.resolveName("column-width"),
            s = this.resolveName("column-gap");
          t = K(e, Js(e, new hn(e, "min", [i, n]), H(e, r, s)), s);
          break;
        }
      }
      return t && (this.namedFuncs[e] = t), t;
    }
    initEnabled() {
      let e = this.pageBox.scope,
        t = this.style,
        i = pg(e, t.enabled, e._true),
        n = te(e, t.page, e.zero);
      if (n) {
        let t = new ee(e, "page-number");
        i = tt(e, i, new Rn(e, n, t));
      }
      let r = te(e, t["min-page-width"], e.zero);
      r && (i = tt(e, i, new ds(e, new ee(e, "page-width"), r)));
      let s = te(e, t["min-page-height"], e.zero);
      s && (i = tt(e, i, new ds(e, new ee(e, "page-height"), s))),
        (i = this.boxSpecificEnabled(i)),
        (t.enabled = new F(i));
    }
    boxSpecificEnabled(e) {
      return e;
    }
    initHorizontal() {
      let e = this.pageBox.scope,
        t = this.style,
        i = this.parentInstance
          ? this.parentInstance.style.width.toExpr(e, null)
          : null,
        n = te(e, t.left, i),
        r = te(e, t["margin-left"], i),
        s = $t(e, t["border-left-width"], t["border-left-style"], i),
        o = ot(e, t["padding-left"], i),
        a = te(e, t.width, i),
        l = te(e, t["max-width"], i),
        h = ot(e, t["padding-right"], i),
        u = $t(e, t["border-right-width"], t["border-right-style"], i),
        c = te(e, t["margin-right"], i),
        d = te(e, t.right, i),
        p = H(e, s, o),
        f = H(e, s, h);
      if (n && d && a) {
        let t = K(e, i, H(e, a, H(e, H(e, n, p), f)));
        r
          ? c
            ? (d = K(e, t, c))
            : (c = K(e, t, H(e, d, r)))
          : ((t = K(e, t, d)),
            c ? (r = K(e, t, c)) : ((r = Js(e, t, new ie(e, 0.5))), (c = r)));
      } else {
        r || (r = e.zero),
          c || (c = e.zero),
          !n && !d && !a && (n = e.zero),
          n || a
            ? n || d
              ? !a && !d && ((a = this.autoWidth), (this.isAutoWidth = !0))
              : (n = e.zero)
            : ((a = this.autoWidth), (this.isAutoWidth = !0));
        let s = K(e, i, H(e, H(e, r, p), H(e, c, f)));
        this.isAutoWidth &&
          (l || (l = K(e, s, n || d)),
          !this.vertical &&
            (te(e, t["column-width"], null) ||
              te(e, t["column-count"], null)) &&
            ((a = l), (this.isAutoWidth = !1))),
          n
            ? a
              ? d || (d = K(e, s, H(e, n, a)))
              : (a = K(e, s, H(e, n, d)))
            : (n = K(e, s, H(e, d, a)));
      }
      let g = ot(
        e,
        t["snap-width"] ||
          (this.parentInstance
            ? this.parentInstance.style["snap-width"]
            : null),
        i
      );
      (t.left = new F(n)),
        (t["margin-left"] = new F(r)),
        (t["border-left-width"] = new F(s)),
        (t["padding-left"] = new F(o)),
        (t.width = new F(a)),
        (t["max-width"] = new F(l || a)),
        (t["padding-right"] = new F(h)),
        (t["border-right-width"] = new F(u)),
        (t["margin-right"] = new F(c)),
        (t.right = new F(d)),
        (t["snap-width"] = new F(g));
    }
    initVertical() {
      let e = this.pageBox.scope,
        t = this.style,
        i = this.parentInstance
          ? this.parentInstance.style.width.toExpr(e, null)
          : null,
        n = this.parentInstance
          ? this.parentInstance.style.height.toExpr(e, null)
          : null,
        r = te(e, t.top, n),
        s = te(e, t["margin-top"], i),
        o = $t(e, t["border-top-width"], t["border-top-style"], i),
        a = ot(e, t["padding-top"], i),
        l = te(e, t.height, n),
        h = te(e, t["max-height"], n),
        u = ot(e, t["padding-bottom"], i),
        c = $t(e, t["border-bottom-width"], t["border-bottom-style"], i),
        d = te(e, t["margin-bottom"], i),
        p = te(e, t.bottom, n),
        f = H(e, o, a),
        g = H(e, c, u);
      if (r && p && l) {
        let t = K(e, n, H(e, l, H(e, H(e, r, f), g)));
        s
          ? d
            ? (p = K(e, t, s))
            : (d = K(e, t, H(e, p, s)))
          : ((t = K(e, t, p)),
            d ? (s = K(e, t, d)) : ((s = Js(e, t, new ie(e, 0.5))), (d = s)));
      } else {
        s || (s = e.zero),
          d || (d = e.zero),
          !r && !p && !l && (r = e.zero),
          r || l
            ? r || p
              ? !l && !p && ((l = this.autoHeight), (this.isAutoHeight = !0))
              : (r = e.zero)
            : ((l = this.autoHeight), (this.isAutoHeight = !0));
        let i = K(e, n, H(e, H(e, s, f), H(e, d, g)));
        this.isAutoHeight &&
          (h || (h = K(e, i, r || p)),
          this.vertical &&
            (te(e, t["column-width"], null) ||
              te(e, t["column-count"], null)) &&
            ((l = h), (this.isAutoHeight = !1))),
          r
            ? l
              ? p || (p = K(e, i, H(e, r, l)))
              : (l = K(e, i, H(e, p, r)))
            : (r = K(e, i, H(e, p, l)));
      }
      let m = ot(
        e,
        t["snap-height"] ||
          (this.parentInstance
            ? this.parentInstance.style["snap-height"]
            : null),
        i
      );
      (t.top = new F(r)),
        (t["margin-top"] = new F(s)),
        (t["border-top-width"] = new F(o)),
        (t["padding-top"] = new F(a)),
        (t.height = new F(l)),
        (t["max-height"] = new F(h || l)),
        (t["padding-bottom"] = new F(u)),
        (t["border-bottom-width"] = new F(c)),
        (t["margin-bottom"] = new F(d)),
        (t.bottom = new F(p)),
        (t["snap-height"] = new F(m));
    }
    initColumns() {
      let e = this.pageBox.scope,
        t = this.style,
        i = te(e, t[this.vertical ? "height" : "width"], null),
        n = te(e, t["column-width"], i),
        r = te(e, t["column-count"], null),
        s = ib(e, t["column-gap"], null);
      s || (s = new Ot(e, 1, "em")),
        n &&
          !r &&
          ((r = new hn(e, "floor", [Yo(e, H(e, i, s), H(e, n, s))])),
          (r = new hn(e, "max", [e.one, r]))),
        r || (r = e.one),
        (n = K(e, Yo(e, H(e, i, s), r), s)),
        (t["column-width"] = new F(n)),
        (t["column-count"] = new F(r)),
        (t["column-gap"] = new F(s));
    }
    depends(e, t, i) {
      return this.style[e].toExpr(this.pageBox.scope, null).depend(t, i);
    }
    init(e) {
      var t;
      e.registerInstance(this.pageBox.key, this);
      let i = this.pageBox.scope,
        n = this.style,
        r = this.parentInstance
          ? this.parentInstance.getActiveRegions(e)
          : null,
        s = Sa(this.cascaded, e, r, !1, null);
      (this.vertical = ya(
        s,
        e,
        !!this.parentInstance && this.parentInstance.vertical
      )),
        (this.rtl = Ea(s, e, !!this.parentInstance && this.parentInstance.rtl)),
        (this.borderBoxSizing =
          (null == (t = s["box-sizing"]) ? void 0 : t.value) === b.border_box);
      let o = !!(
        e instanceof ss &&
        e.currentLayoutPosition &&
        new ee(i, "left-page").evaluate(e)
      );
      Na(s, n, this.vertical, this.rtl, o, (e, t) => t.value),
        (this.autoWidth = new Z(i, () => this.calculatedWidth, "autoWidth")),
        (this.autoHeight = new Z(i, () => this.calculatedHeight, "autoHeight")),
        this.initHorizontal(),
        this.initVertical(),
        this.initColumns(),
        this.initEnabled();
    }
    getProp(e, t) {
      var i, n;
      let r = this.style[t];
      return (
        !r &&
          Zn(t) &&
          this.pageMasterInstance instanceof un &&
          (r =
            "font-size" === t && e.isRelativeRootFontSize && e.rootFontSize
              ? new P(e.rootFontSize, "px")
              : null == (n = (null == (i = e.styler) ? void 0 : i.rootStyle)[t])
              ? void 0
              : n.value),
        r && (r = Ei(e, r, t)),
        r
      );
    }
    getPropAsNumber(e, t) {
      var i, n;
      let r = this.style[t];
      if (r) {
        let s = /\b(height|top|bottom)\b/.test(t)
          ? null != (i = e.pageAreaHeight)
            ? i
            : e.pageHeight()
          : null != (n = e.pageAreaWidth)
          ? n
          : e.pageWidth();
        r = Ei(e, r, t, s);
      }
      return ke(r, e);
    }
    getSpecial(e, t) {
      let i = Xu(this.cascaded, t);
      if (i) {
        let t = [];
        for (let n = 0; n < i.length; n++) {
          let r = i[n].evaluate(e, "");
          r && r !== O && t.push(r);
        }
        if (t.length) return t;
      }
      return null;
    }
    getActiveRegions(e) {
      let t = this.getSpecial(e, "region-id");
      if (t) {
        let e = [];
        for (let i = 0; i < t.length; i++) e[i] = t[i].toString();
        return e;
      }
      return null;
    }
    propagateProperty(e, t, i, n) {
      this.propagatePropertyToElement(e, t.element, i, n);
    }
    propagatePropertyToElement(e, t, i, n) {
      let r = this.getProp(e, i);
      r &&
        (r.isNumeric() && Tr(r.unit) && (r = kr(r, e)),
        "font-family" === i && (r = n.filterFontFamily(r)),
        (i.startsWith("background") || "z-index" === i) &&
          t.parentElement.hasAttribute("data-vivliostyle-bleed-box") &&
          (t = t.parentElement),
        w(t, i, r.toString()));
    }
    propagateDelayedProperty(e, t, i, n) {
      let r = this.getProp(e, i);
      r && n.push(new Hn(t.element, i, r));
    }
    assignLeftPosition(e, t) {
      let i = this.getPropAsNumber(e, "left"),
        n = this.getPropAsNumber(e, "margin-left"),
        r = this.getPropAsNumber(e, "padding-left"),
        s = this.getPropAsNumber(e, "border-left-width"),
        o = this.getPropAsNumber(e, "width");
      t.setHorizontalPosition(i, o),
        w(t.element, "margin-left", `${n}px`),
        w(t.element, "padding-left", `${r}px`),
        w(t.element, "border-left-width", `${s}px`),
        (t.marginLeft = n),
        (t.borderLeft = s),
        (t.paddingLeft = r);
    }
    assignRightPosition(e, t) {
      let i = this.getPropAsNumber(e, "right"),
        n = this.getPropAsNumber(e, "snap-height"),
        r = this.getPropAsNumber(e, "margin-right"),
        s = this.getPropAsNumber(e, "padding-right"),
        o = this.getPropAsNumber(e, "border-right-width");
      if (
        (w(t.element, "margin-right", `${r}px`),
        w(t.element, "padding-right", `${s}px`),
        w(t.element, "border-right-width", `${o}px`),
        (t.marginRight = r),
        (t.borderRight = o),
        this.vertical && n > 0)
      ) {
        let e = i + t.getInsetRight(),
          r = e - Math.floor(e / n) * n;
        r > 0 && ((t.snapOffsetX = n - r), (s += t.snapOffsetX));
      }
      (t.paddingRight = s), (t.snapWidth = n);
    }
    assignTopPosition(e, t) {
      let i = this.getPropAsNumber(e, "snap-height"),
        n = this.getPropAsNumber(e, "top"),
        r = this.getPropAsNumber(e, "margin-top"),
        s = this.getPropAsNumber(e, "padding-top"),
        o = this.getPropAsNumber(e, "border-top-width");
      if (
        ((t.top = n),
        (t.marginTop = r),
        (t.borderTop = o),
        (t.snapHeight = i),
        !this.vertical && i > 0)
      ) {
        let e = n + t.getInsetTop(),
          r = e - Math.floor(e / i) * i;
        r > 0 && ((t.snapOffsetY = i - r), (s += t.snapOffsetY));
      }
      (t.paddingTop = s),
        w(t.element, "top", `${n}px`),
        w(t.element, "margin-top", `${r}px`),
        w(t.element, "padding-top", `${s}px`),
        w(t.element, "border-top-width", `${o}px`);
    }
    assignBottomPosition(e, t) {
      let i = this.getPropAsNumber(e, "margin-bottom"),
        n = this.getPropAsNumber(e, "padding-bottom"),
        r = this.getPropAsNumber(e, "border-bottom-width"),
        s = this.getPropAsNumber(e, "height") - t.snapOffsetY;
      w(t.element, "height", `${s}px`),
        w(t.element, "margin-bottom", `${i}px`),
        w(t.element, "padding-bottom", `${n}px`),
        w(t.element, "border-bottom-width", `${r}px`),
        (t.height = s - t.snapOffsetY),
        (t.marginBottom = i),
        (t.borderBottom = r),
        (t.paddingBottom = n);
    }
    assignBeforePosition(e, t) {
      this.vertical
        ? this.assignRightPosition(e, t)
        : this.assignTopPosition(e, t);
    }
    assignAfterPosition(e, t) {
      this.vertical
        ? this.assignLeftPosition(e, t)
        : this.assignBottomPosition(e, t);
    }
    assignStartEndPosition(e, t) {
      this.vertical
        ? (this.assignTopPosition(e, t), this.assignBottomPosition(e, t))
        : (this.assignRightPosition(e, t), this.assignLeftPosition(e, t));
    }
    sizeWithMaxHeight(e, t) {
      w(t.element, "border-top-width", "0px");
      let i = this.getPropAsNumber(e, "max-height");
      this.isTopDependentOnAutoHeight
        ? t.setVerticalPosition(0, i)
        : (this.assignTopPosition(e, t),
          (i -= t.snapOffsetY),
          (t.height = i),
          w(t.element, "height", `${i}px`));
    }
    sizeWithMaxWidth(e, t) {
      w(t.element, "border-left-width", "0px");
      let i = this.getPropAsNumber(e, "max-width");
      if (this.isRightDependentOnAutoWidth) t.setHorizontalPosition(0, i);
      else {
        this.assignRightPosition(e, t), (i -= t.snapOffsetX), (t.width = i);
        let n = this.getPropAsNumber(e, "right");
        w(t.element, "right", `${n}px`), w(t.element, "width", `${i}px`);
      }
    }
    prepareContainer(e, t, i, n, r) {
      (!this.parentInstance || this.vertical != this.parentInstance.vertical) &&
        w(
          t.element,
          "writing-mode",
          this.vertical ? "vertical-rl" : "horizontal-tb"
        ),
        (!this.parentInstance || this.rtl != this.parentInstance.rtl) &&
          w(t.element, "direction", this.rtl ? "rtl" : "ltr"),
        (this.vertical ? this.isAutoWidth : this.isAutoHeight)
          ? this.vertical
            ? this.sizeWithMaxWidth(e, t)
            : this.sizeWithMaxHeight(e, t)
          : (this.assignBeforePosition(e, t), this.assignAfterPosition(e, t)),
        (this.vertical ? this.isAutoHeight : this.isAutoWidth)
          ? this.vertical
            ? this.sizeWithMaxHeight(e, t)
            : this.sizeWithMaxWidth(e, t)
          : this.assignStartEndPosition(e, t);
      for (let i = 0; i < ag.length; i++)
        this.propagateProperty(e, t, ag[i], n);
    }
    transferContentProps(e, t, i, n) {
      for (let i = 0; i < cg.length; i++)
        this.propagateProperty(e, t, cg[i], n);
      if (this.style["text-overflow"]) {
        let e = t.element.firstElementChild;
        w(e, "text-overflow", "inherit"), w(e, "overflow", "inherit");
      }
    }
    transferSingleUriContentProps(e, t, i) {
      let n = () => {
          var e, t;
          let i =
            null ==
            (t =
              null != (e = this.cascaded.width)
                ? e
                : this.cascaded[this.vertical ? "block-size" : "inline-size"])
              ? void 0
              : t.value;
          return !!i && i !== b.auto && !M(i);
        },
        r = () => {
          var e, t;
          let i =
            null ==
            (t =
              null != (e = this.cascaded.height)
                ? e
                : this.cascaded[this.vertical ? "inline-size" : "block-size"])
              ? void 0
              : t.value;
          return !!i && i !== b.auto && !M(i);
        };
      for (let s = 0; s < ug.length; s++) {
        let o = ug[s];
        if ("width" !== o && "height" !== o)
          this.propagatePropertyToElement(e, t, o, i);
        else {
          if (("width" === o && !n()) || ("height" === o && !r())) continue;
          w(t, o, "100%");
        }
      }
    }
    finishContainer(e, t, i, n, r, s, o) {
      this.vertical
        ? (this.calculatedWidth = t.computedBlockSize + t.snapOffsetX)
        : (this.calculatedHeight = t.computedBlockSize + t.snapOffsetY);
      let a = (this.vertical || !n) && this.isAutoHeight,
        l = (!this.vertical || !n) && this.isAutoWidth,
        h = null;
      if (l || a) {
        l && w(t.element, "width", "auto"), a && w(t.element, "height", "auto");
        let e = n && jc(n);
        e && Xc(n),
          (h = s.getElementClientRect(n ? n.element : t.element)),
          e && di(n),
          l &&
            ((this.calculatedWidth = Math.ceil(
              h.right -
                h.left -
                t.paddingLeft -
                t.borderLeft -
                t.paddingRight -
                t.borderRight
            )),
            this.vertical && (this.calculatedWidth += t.snapOffsetX)),
          a &&
            ((this.calculatedHeight =
              h.bottom -
              h.top -
              t.paddingTop -
              t.borderTop -
              t.paddingBottom -
              t.borderBottom),
            this.vertical || (this.calculatedHeight += t.snapOffsetY));
      }
      if (
        ((this.vertical ? this.isAutoHeight : this.isAutoWidth) &&
          this.assignStartEndPosition(e, t),
        (this.vertical ? this.isAutoWidth : this.isAutoHeight) &&
          ((this.vertical
            ? this.isRightDependentOnAutoWidth
            : this.isTopDependentOnAutoHeight) &&
            this.assignBeforePosition(e, t),
          this.assignAfterPosition(e, t)),
        r > 1)
      ) {
        let i = this.getPropAsNumber(e, "column-rule-width"),
          n = this.getProp(e, "column-rule-style"),
          s = this.getProp(e, "column-rule-color");
        if (i > 0 && n && n != b.none && s != b.transparent) {
          let o = this.getPropAsNumber(e, "column-gap"),
            a = this.vertical ? t.height : t.width,
            l = this.vertical ? "border-top" : "border-left";
          for (let e = 1; e < r; e++) {
            let h = this.vertical
                ? ((a + o) * e) / r - o / 2 + t.paddingTop - i / 2
                : ((a + o) * e) / r - o / 2 + t.paddingLeft - i / 2,
              u = this.vertical
                ? t.width + t.paddingLeft + t.paddingRight
                : t.height + t.paddingTop + t.paddingBottom,
              c = t.element.ownerDocument.createElement("div");
            w(c, "position", "absolute"),
              w(c, this.vertical ? "left" : "top", "0px"),
              w(c, this.vertical ? "top" : "left", `${h}px`),
              w(c, this.vertical ? "height" : "width", "0px"),
              w(c, this.vertical ? "width" : "height", `${u}px`),
              w(c, l, `${i}px ${n.toString()}${s ? ` ${s.toString()}` : ""}`),
              t.element.insertBefore(c, t.element.firstChild);
          }
        }
      }
      for (let i = 0; i < lg.length; i++)
        this.propagateProperty(e, t, lg[i], o);
      for (let n = 0; n < dg.length; n++)
        this.propagateDelayedProperty(e, t, dg[n], i.delayedItems);
    }
    applyCascadeAndInit(e, t) {
      let i = this.cascaded,
        n = this.pageBox.specified;
      for (let e in n)
        (this instanceof un &&
          "vivliostyle-page-rule-master" == this.pageBox.pseudoName &&
          ("writing-mode" === e || "direction" === e)) ||
          (Ls(e) && En(i, e, he(n, e)));
      if (this.pageBox.pseudoName == Mi)
        for (let e in t)
          (e.match(/^background-/) || "writing-mode" == e) && (i[e] = t[e]);
      if ("layout-host" == this.pageBox.pseudoName)
        for (let e in t)
          !e.match(/^background-/) && "writing-mode" != e && (i[e] = t[e]);
      e.pushRule(this.pageBox.classes, null, i);
      let r = i.content;
      r && (i.content = r.filterValue(new vo(e, null, e.counterResolver))),
        this.init(e.context);
      for (let i of this.pageBox.children)
        i.createInstance(this).applyCascadeAndInit(e, t);
      e.popRule();
    }
    resolveAutoSizing(e) {
      this.isAutoWidth &&
        (this.isRightDependentOnAutoWidth =
          this.depends("right", this.autoWidth, e) ||
          this.depends("margin-right", this.autoWidth, e) ||
          this.depends("border-right-width", this.autoWidth, e) ||
          this.depends("padding-right", this.autoWidth, e)),
        this.isAutoHeight &&
          (this.isTopDependentOnAutoHeight =
            this.depends("top", this.autoHeight, e) ||
            this.depends("margin-top", this.autoHeight, e) ||
            this.depends("border-top-width", this.autoHeight, e) ||
            this.depends("padding-top", this.autoHeight, e));
      for (let t of this.children) t.resolveAutoSizing(e);
    }
  },
  ag = [
    "border-left-style",
    "border-right-style",
    "border-top-style",
    "border-bottom-style",
    "border-left-color",
    "border-right-color",
    "border-top-color",
    "border-bottom-color",
    "box-sizing",
    "outline-style",
    "outline-color",
    "outline-width",
    "overflow",
    "visibility",
  ],
  lg = [
    "border-top-left-radius",
    "border-top-right-radius",
    "border-bottom-right-radius",
    "border-bottom-left-radius",
    "border-start-start-radius",
    "border-start-end-radius",
    "border-end-start-radius",
    "border-end-end-radius",
    "border-image-source",
    "border-image-slice",
    "border-image-width",
    "border-image-outset",
    "border-image-repeat",
    "background-attachment",
    "background-color",
    "background-image",
    "background-repeat",
    "background-position",
    "background-clip",
    "background-origin",
    "background-size",
    "box-shadow",
    "opacity",
    "z-index",
    "background-blend-mode",
    "isolation",
    "mix-blend-mode",
    "filter",
  ],
  cg = [
    "color",
    "font-family",
    "font-size",
    "font-style",
    "font-weight",
    "line-height",
    "letter-spacing",
    "text-align",
    "text-align-last",
    "text-decoration",
    "text-indent",
    "text-transform",
    "white-space",
    "text-wrap",
    "text-wrap-mode",
    "text-wrap-style",
    "word-spacing",
    "font-feature-settings",
    "font-kerning",
    "font-size-adjust",
    "font-variant-ligatures",
    "font-variant-caps",
    "font-variant-numeric",
    "font-variant-east-asian",
    "font-stretch",
    "text-combine-upright",
    "text-decoration-color",
    "text-decoration-line",
    "text-decoration-skip",
    "text-decoration-skip-ink",
    "text-decoration-style",
    "text-decoration-thickness",
    "text-emphasis",
    "text-emphasis-color",
    "text-emphasis-position",
    "text-emphasis-style",
    "text-fill-color",
    "text-orientation",
    "text-shadow",
    "text-stroke-color",
    "text-stroke-width",
    "text-underline-offset",
    "text-underline-position",
    "text-overflow",
  ],
  ug = ["width", "height", "image-resolution", "object-fit", "object-position"],
  dg = ["transform", "transform-origin"],
  Mi = "background-host",
  Ga = class extends Vo {
    constructor(e) {
      super(null, e);
    }
    applyCascadeAndInit(e, t) {
      super.applyCascadeAndInit(e, t),
        this.children.sort(
          (e, t) =>
            t.pageBox.specificity - e.pageBox.specificity ||
            e.pageBox.index - t.pageBox.index
        );
    }
  },
  Hs = class extends Vo {
    constructor(e, t) {
      super(e, t), (this.pageMasterInstance = this);
    }
    boxSpecificEnabled(e) {
      let t = this.pageBox.pageMaster;
      return t.condition && (e = tt(t.scope, e, t.condition)), e;
    }
    adjustPageLayout(e, t, i) {}
  },
  Kd = class extends Vo {
    constructor(e, t) {
      super(e, t), (this.pageMasterInstance = e.pageMasterInstance);
    }
  },
  cn = class extends Vo {
    constructor(e, t) {
      super(e, t), (this.pageMasterInstance = e.pageMasterInstance);
    }
    processPartitionList(e, t, i) {
      let n = null;
      if (
        (t instanceof be && (n = [t]), t instanceof ge && (n = t.values), n)
      ) {
        let t = this.pageBox.scope;
        for (let r = 0; r < n.length; r++)
          if (n[r] instanceof be) {
            let s = jo(n[r].name, "enabled"),
              o = new ee(t, s);
            i && (o = new mt(t, o)), (e = tt(t, e, o));
          }
      }
      return e;
    }
    boxSpecificEnabled(e) {
      let t = this.pageBox.scope,
        i = this.style,
        n = pg(t, i.required, t._false) !== t._false;
      if (n || this.isAutoHeight) {
        let n = ob(t, i["flow-from"], "body");
        e = tt(t, e, new hn(t, "has-content", [n]));
      }
      if (
        ((e = this.processPartitionList(e, i["required-partitions"], !1)),
        (e = this.processPartitionList(e, i["conflicting-partitions"], !0)),
        n)
      ) {
        let i = this.pageMasterInstance.style.enabled,
          n = i ? i.toExpr(t, null) : t._true;
        (n = tt(t, n, e)), (this.pageMasterInstance.style.enabled = new F(n));
      }
      return e;
    }
    prepareContainer(e, t, i, n, r) {
      var s;
      this.pageBox.pageMaster instanceof _i ||
        (w(t.element, "overflow", "hidden"),
        t.element.setAttribute(
          "data-vivliostyle-page-partition",
          null != (s = this.pageBox.name) ? s : ""
        )),
        super.prepareContainer(e, t, i, n, r);
    }
  },
  Di = class extends nn {
    constructor(e, t, i, n) {
      super(e, t, !1), (this.target = i), (this.validatorSet = n);
    }
    property(e, t, i) {
      this.validatorSet.validatePropertyAndHandleShorthand(e, t, i, this);
    }
    unknownProperty(e, t) {
      this.report(`E_INVALID_PROPERTY ${e}: ${t.toString()}`);
    }
    invalidPropertyValue(e, t) {
      this.report(`E_INVALID_PROPERTY_VALUE ${e}: ${t.toString()}`);
    }
    simpleProperty(e, t, i) {
      this.target.specified[e] = new _(t, i ? Ur : Hr);
    }
  },
  Wa = class extends Di {
    constructor(e, t, i, n) {
      super(e, t, i, n);
    }
  },
  Zd = class e extends Di {
    constructor(e, t, i, n) {
      super(e, t, i, n),
        (i.specified.width = new _(Gl, 0)),
        (i.specified.height = new _(Gl, 0));
    }
    startPartitionRule(e, t, i) {
      let n = new ns(this.scope, e, t, i, this.target),
        r = new Wa(this.scope, this.owner, n, this.validatorSet);
      this.owner.pushHandler(r);
    }
    startPartitionGroupRule(t, i, n) {
      let r = new za(this.scope, t, i, n, this.target),
        s = new e(this.scope, this.owner, r, this.validatorSet);
      this.owner.pushHandler(s);
    }
  },
  $a = class extends Di {
    constructor(e, t, i, n) {
      super(e, t, i, n);
    }
    startPartitionRule(e, t, i) {
      let n = new ns(this.scope, e, t, i, this.target),
        r = new Wa(this.scope, this.owner, n, this.validatorSet);
      this.owner.pushHandler(r);
    }
    startPartitionGroupRule(e, t, i) {
      let n = new za(this.scope, e, t, i, this.target),
        r = new Zd(this.scope, this.owner, n, this.validatorSet);
      this.owner.pushHandler(r);
    }
  };
function mp(e) {
  var t, i;
  let n = null == (t = e["writing-mode"]) ? void 0 : t.value,
    r = null == (i = e.direction) ? void 0 : i.value;
  return n === b.vertical_lr || (n !== b.vertical_rl && r !== b.rtl)
    ? "ltr"
    : "rtl";
}
var rb = {
    a10: { width: new P(26, "mm"), height: new P(37, "mm") },
    a9: { width: new P(37, "mm"), height: new P(52, "mm") },
    a8: { width: new P(52, "mm"), height: new P(74, "mm") },
    a7: { width: new P(74, "mm"), height: new P(105, "mm") },
    a6: { width: new P(105, "mm"), height: new P(148, "mm") },
    a5: { width: new P(148, "mm"), height: new P(210, "mm") },
    a4: { width: new P(210, "mm"), height: new P(297, "mm") },
    a3: { width: new P(297, "mm"), height: new P(420, "mm") },
    a2: { width: new P(420, "mm"), height: new P(594, "mm") },
    a1: { width: new P(594, "mm"), height: new P(841, "mm") },
    a0: { width: new P(841, "mm"), height: new P(1189, "mm") },
    b10: { width: new P(31, "mm"), height: new P(44, "mm") },
    b9: { width: new P(44, "mm"), height: new P(62, "mm") },
    b8: { width: new P(62, "mm"), height: new P(88, "mm") },
    b7: { width: new P(88, "mm"), height: new P(125, "mm") },
    b6: { width: new P(125, "mm"), height: new P(176, "mm") },
    b5: { width: new P(176, "mm"), height: new P(250, "mm") },
    b4: { width: new P(250, "mm"), height: new P(353, "mm") },
    b3: { width: new P(353, "mm"), height: new P(500, "mm") },
    b2: { width: new P(500, "mm"), height: new P(707, "mm") },
    b1: { width: new P(707, "mm"), height: new P(1e3, "mm") },
    b0: { width: new P(1e3, "mm"), height: new P(1414, "mm") },
    c10: { width: new P(28, "mm"), height: new P(40, "mm") },
    c9: { width: new P(40, "mm"), height: new P(57, "mm") },
    c8: { width: new P(57, "mm"), height: new P(81, "mm") },
    c7: { width: new P(81, "mm"), height: new P(114, "mm") },
    c6: { width: new P(114, "mm"), height: new P(162, "mm") },
    c5: { width: new P(162, "mm"), height: new P(229, "mm") },
    c4: { width: new P(229, "mm"), height: new P(324, "mm") },
    c3: { width: new P(324, "mm"), height: new P(458, "mm") },
    c2: { width: new P(458, "mm"), height: new P(648, "mm") },
    c1: { width: new P(648, "mm"), height: new P(917, "mm") },
    c0: { width: new P(917, "mm"), height: new P(1297, "mm") },
    "jis-b10": { width: new P(32, "mm"), height: new P(45, "mm") },
    "jis-b9": { width: new P(45, "mm"), height: new P(64, "mm") },
    "jis-b8": { width: new P(64, "mm"), height: new P(91, "mm") },
    "jis-b7": { width: new P(91, "mm"), height: new P(128, "mm") },
    "jis-b6": { width: new P(128, "mm"), height: new P(182, "mm") },
    "jis-b5": { width: new P(182, "mm"), height: new P(257, "mm") },
    "jis-b4": { width: new P(257, "mm"), height: new P(364, "mm") },
    "jis-b3": { width: new P(364, "mm"), height: new P(515, "mm") },
    "jis-b2": { width: new P(515, "mm"), height: new P(728, "mm") },
    "jis-b1": { width: new P(728, "mm"), height: new P(1030, "mm") },
    "jis-b0": { width: new P(1030, "mm"), height: new P(1456, "mm") },
    letter: { width: new P(8.5, "in"), height: new P(11, "in") },
    legal: { width: new P(8.5, "in"), height: new P(14, "in") },
    ledger: { width: new P(11, "in"), height: new P(17, "in") },
  },
  ab = new P(0.24, "pt"),
  dy = new P(3, "mm"),
  lb = new P(10, "mm"),
  cb = new P(13, "mm");
function $i(e) {
  let t = { width: Vn, height: Fn, bleed: ne, bleedOffset: ne, cropOffset: ne },
    i = e.size;
  if (i && i.value !== b.auto) {
    let e,
      n,
      r = i.value;
    if (
      (r.isSpaceList()
        ? ((e = r.values[0]), (n = r.values[1]))
        : ((e = r), (n = null)),
      e.isNumeric())
    )
      (t.width = e), (t.height = n || e);
    else {
      let i = e.name && rb[e.name.toLowerCase()];
      i &&
        (n && n === b.landscape
          ? ((t.width = i.height), (t.height = i.width))
          : ((t.width = i.width), (t.height = i.height)));
    }
  }
  let n = e.marks,
    r = e.bleed,
    s = n && !M(n.value) ? n.value : b.none,
    o = r && !M(r.value) ? r.value : b.auto;
  if (o === b.auto) {
    if (s !== b.none) {
      let e = !1;
      (e = s.isSpaceList() ? s.values.some((e) => e === b.crop) : s === b.crop),
        e && (t.bleed = new P(6, "pt"));
    }
  } else o.isNumeric() && (t.bleed = o);
  let a = e["crop-offset"],
    l = a && !M(a.value) ? a.value : b.auto;
  return (
    l === b.auto
      ? s !== b.none && (t.bleedOffset = cb)
      : l.isNumeric() && (t.cropOffset = l),
    t
  );
}
function Cp(e, t) {
  let i = {},
    n = Math.max(0, e.bleed.num) * t.queryUnitSize(e.bleed.unit, !1),
    r =
      !e.cropOffset.num && e.bleedOffset.num
        ? e.bleedOffset.num * t.queryUnitSize(e.bleedOffset.unit, !1)
        : Math.max(
            0,
            e.cropOffset.num * t.queryUnitSize(e.cropOffset.unit, !1) - n
          ),
    s = n + r,
    o = e.width;
  o === Vn
    ? t.pref.defaultPaperSize
      ? (i.pageWidth =
          t.pref.defaultPaperSize.width * t.queryUnitSize("px", !1))
      : (i.pageWidth =
          (t.pref.spreadView
            ? Math.floor(t.viewportWidth / 2) - t.pref.pageBorder
            : t.viewportWidth) -
          2 * s)
    : (i.pageWidth = o.num * t.queryUnitSize(o.unit, !1));
  let a = e.height;
  return (
    a === Fn
      ? t.pref.defaultPaperSize
        ? (i.pageHeight =
            t.pref.defaultPaperSize.height * t.queryUnitSize("px", !1))
        : (i.pageHeight = t.viewportHeight - 2 * s)
      : (i.pageHeight = a.num * t.queryUnitSize(a.unit, !1)),
    (i.bleed = n),
    (i.bleedOffset = r),
    (i.cropOffset = s),
    i
  );
}
function fg(e, t, i) {
  let n = e.createElementNS("http://www.w3.org/2000/svg", "svg");
  return (
    n.setAttribute("width", t),
    n.setAttribute("height", i),
    (n.style.position = "absolute"),
    n
  );
}
function Ui(e, t, i, n) {
  n = n || "polyline";
  let r = e.createElementNS("http://www.w3.org/2000/svg", n);
  return (
    r.setAttribute("stroke", i === b.auto ? "#010101" : i.toString()),
    r.setAttribute("stroke-width", t),
    r.setAttribute("fill", "none"),
    r
  );
}
var Jd = ((e) => (
  (e.TOP_LEFT = "top left"),
  (e.TOP_RIGHT = "top right"),
  (e.BOTTOM_LEFT = "bottom left"),
  (e.BOTTOM_RIGHT = "bottom right"),
  e
))(Jd || {});
function ub(e, t, i, n, r, s, o) {
  let a = r;
  a <= s + 2 * Y.mm && (a = s + r / 2);
  let l = Math.max(r, a),
    h = s + l + i / 2,
    u = fg(e, h, h),
    c = [
      [0, s + r],
      [r, s + r],
      [r, s + r - a],
    ],
    d = c.map((e) => [e[1], e[0]]);
  ("top right" === t || "bottom right" === t) &&
    ((c = c.map((e) => [s + l - e[0], e[1]])),
    (d = d.map((e) => [s + l - e[0], e[1]]))),
    ("bottom left" === t || "bottom right" === t) &&
      ((c = c.map((e) => [e[0], s + l - e[1]])),
      (d = d.map((e) => [e[0], s + l - e[1]])));
  let p = Ui(e, i, n);
  p.setAttribute("points", c.map((e) => e.join(",")).join(" ")),
    u.appendChild(p);
  let f = Ui(e, i, n);
  return (
    f.setAttribute("points", d.map((e) => e.join(",")).join(" ")),
    u.appendChild(f),
    t.split(" ").forEach((e) => {
      u.style[e] = `${o}px`;
    }),
    u
  );
}
var Hi = ((e) => (
  (e.TOP = "top"),
  (e.BOTTOM = "bottom"),
  (e.LEFT = "left"),
  (e.RIGHT = "right"),
  e
))(Hi || {});
function db(e, t, i, n, r, s) {
  let o,
    a,
    l = 2 * r;
  "top" === t || "bottom" === t ? ((o = l), (a = r)) : ((o = r), (a = l));
  let h = fg(e, o, a),
    u = Ui(e, i, n);
  u.setAttribute("points", `0,${a / 2} ${o},${a / 2}`), h.appendChild(u);
  let c = Ui(e, i, n);
  c.setAttribute("points", `${o / 2},0 ${o / 2},${a}`), h.appendChild(c);
  let d,
    p = Ui(e, i, n, "circle");
  switch (
    (p.setAttribute("cx", o / 2),
    p.setAttribute("cy", a / 2),
    p.setAttribute("r", r / 4),
    h.appendChild(p),
    t)
  ) {
    case "top":
      d = "bottom";
      break;
    case "bottom":
      d = "top";
      break;
    case "left":
      d = "right";
      break;
    case "right":
      d = "left";
  }
  return (
    Object.keys(Hi).forEach((e) => {
      let i = Hi[e];
      i === t
        ? (h.style[i] = `${s}px`)
        : i !== d && ((h.style[i] = "0"), (h.style[`margin-${i}`] = "auto"));
    }),
    h
  );
}
function gg(e, t, i, n) {
  let r = !1,
    s = !1,
    o = e.marks;
  if (o) {
    let e = o.value;
    e instanceof q
      ? e.values.forEach((e) => {
          e === b.crop ? (r = !0) : e === b.cross && (s = !0);
        })
      : e === b.crop
      ? (r = !0)
      : e === b.cross && (s = !0);
  }
  let a = t.bleed;
  if (!r && !s) return;
  let l = i.container,
    h = l.ownerDocument,
    u = ke(ab, n),
    c = Math.max(0, t.bleedOffset - ke(lb, n)),
    d = t.bleedOffset - c,
    p = e["crop-marks-line-color"],
    f = p && !M(p.value) ? p.value : b.auto;
  r &&
    Object.keys(Jd).forEach((e) => {
      let t = Jd[e],
        i = ub(h, t, u, f, d, a, c);
      l.appendChild(i);
    }),
    s &&
      Object.keys(Hi).forEach((e) => {
        let t = Hi[e],
          i = db(h, t, u, f, d, c);
        l.appendChild(i);
      });
}
var ep = (() => {
    let e = {
      width: !0,
      height: !0,
      "block-size": !0,
      "inline-size": !0,
      margin: !0,
      padding: !0,
      border: !0,
      outline: !0,
      "outline-width": !0,
      "outline-style": !0,
      "outline-color": !0,
      "border-radius": !0,
      "border-top-left-radius": !0,
      "border-top-right-radius": !0,
      "border-bottom-right-radius": !0,
      "border-bottom-left-radius": !0,
      "border-start-start-radius": !0,
      "border-start-end-radius": !0,
      "border-end-start-radius": !0,
      "border-end-end-radius": !0,
      "box-shadow": !0,
      "box-sizing": !0,
    };
    return (
      [
        "left",
        "right",
        "top",
        "bottom",
        "before",
        "after",
        "start",
        "end",
        "block-start",
        "block-end",
        "inline-start",
        "inline-end",
        "inside",
        "outside",
      ].forEach((t) => {
        (e[`margin-${t}`] = !0),
          (e[`padding-${t}`] = !0),
          (e[`border-${t}-width`] = !0),
          (e[`border-${t}-style`] = !0),
          (e[`border-${t}-color`] = !0);
      }),
      e
    );
  })(),
  bp = {
    "top-left-corner": {
      order: 1,
      isInTopRow: !0,
      isInBottomRow: !1,
      isInLeftColumn: !0,
      isInRightColumn: !1,
      positionAlongVariableDimension: null,
    },
    "top-left": {
      order: 2,
      isInTopRow: !0,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !1,
      positionAlongVariableDimension: "start",
    },
    "top-center": {
      order: 3,
      isInTopRow: !0,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !1,
      positionAlongVariableDimension: "center",
    },
    "top-right": {
      order: 4,
      isInTopRow: !0,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !1,
      positionAlongVariableDimension: "end",
    },
    "top-right-corner": {
      order: 5,
      isInTopRow: !0,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !0,
      positionAlongVariableDimension: null,
    },
    "right-top": {
      order: 6,
      isInTopRow: !1,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !0,
      positionAlongVariableDimension: "start",
    },
    "right-middle": {
      order: 7,
      isInTopRow: !1,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !0,
      positionAlongVariableDimension: "center",
    },
    "right-bottom": {
      order: 8,
      isInTopRow: !1,
      isInBottomRow: !1,
      isInLeftColumn: !1,
      isInRightColumn: !0,
      positionAlongVariableDimension: "end",
    },
    "bottom-right-corner": {
      order: 9,
      isInTopRow: !1,
      isInBottomRow: !0,
      isInLeftColumn: !1,
      isInRightColumn: !0,
      positionAlongVariableDimension: null,
    },
    "bottom-right": {
      order: 10,
      isInTopRow: !1,
      isInBottomRow: !0,
      isInLeftColumn: !1,
      isInRightColumn: !1,
      positionAlongVariableDimension: "end",
    },
    "bottom-center": {
      order: 11,
      isInTopRow: !1,
      isInBottomRow: !0,
      isInLeftColumn: !1,
      isInRightColumn: !1,
      positionAlongVariableDimension: "center",
    },
    "bottom-left": {
      order: 12,
      isInTopRow: !1,
      isInBottomRow: !0,
      isInLeftColumn: !1,
      isInRightColumn: !1,
      positionAlongVariableDimension: "start",
    },
    "bottom-left-corner": {
      order: 13,
      isInTopRow: !1,
      isInBottomRow: !0,
      isInLeftColumn: !0,
      isInRightColumn: !1,
      positionAlongVariableDimension: null,
    },
    "left-bottom": {
      order: 14,
      isInTopRow: !1,
      isInBottomRow: !1,
      isInLeftColumn: !0,
      isInRightColumn: !1,
      positionAlongVariableDimension: "end",
    },
    "left-middle": {
      order: 15,
      isInTopRow: !1,
      isInBottomRow: !1,
      isInLeftColumn: !0,
      isInRightColumn: !1,
      positionAlongVariableDimension: "center",
    },
    "left-top": {
      order: 16,
      isInTopRow: !1,
      isInBottomRow: !1,
      isInLeftColumn: !0,
      isInRightColumn: !1,
      positionAlongVariableDimension: "start",
    },
  },
  pb = (() => {
    let e = bp;
    return Object.keys(e).sort((t, i) => e[t].order - e[i].order);
  })(),
  qa = "vivliostyle-page-rule-master",
  zi = "_marginBoxes",
  _i = class extends Io {
    constructor(e, t, i) {
      super(e, null, qa, [], t, null, 0), p(this, "pageMarginBoxes", {});
      let n = $i(i);
      new tp(this.scope, this, i, n);
      this.createPageMarginBoxes(i), this.applySpecified(i, n);
    }
    createPageMarginBoxes(e) {
      let t = e[zi];
      t &&
        pb.forEach((i) => {
          t[i] && (this.pageMarginBoxes[i] = new sp(this.scope, this, i, e));
        });
    }
    applySpecified(e, t) {
      (this.specified.position = new _(b.relative, 0)),
        (this.specified.width = new _(t.width, 0)),
        (this.specified.height = new _(t.height, 0));
      for (let t in e)
        !ep[t] && "background-clip" !== t && (this.specified[t] = e[t]);
    }
    createInstance(e) {
      return new un(e, this);
    }
  },
  tp = class extends ns {
    constructor(e, t, i, n) {
      super(e, null, null, [], t), (this.pageSize = n);
      new np(this.scope, this);
      this.applySpecified(i);
    }
    applySpecified(e) {
      (this.specified["wrap-flow"] = new _(b.auto, 0)),
        (this.specified["z-index"] = new _(new ut(0), 0)),
        (this.specified.position = new _(b.absolute, 0)),
        (this.specified.overflow = new _(b.visible, 0));
      for (let t in ep) ep.hasOwnProperty(t) && (this.specified[t] = e[t]);
    }
    createInstance(e) {
      return new ip(e, this);
    }
  },
  np = class extends ns {
    constructor(e, t) {
      super(e, null, null, [], t),
        (this.specified["flow-from"] = new _(L("body"), 0));
    }
    createInstance(e) {
      return new Gi(e, this);
    }
  },
  sp = class extends ns {
    constructor(e, t, i, n) {
      super(e, null, null, [], t),
        (this.marginBoxName = i),
        this.applySpecified(n);
    }
    applySpecified(e) {
      let t = e[zi][this.marginBoxName];
      for (let i in e) {
        let n = e[i],
          r = t[i];
        (Gu[i] || (r && r.value === b.inherit)) && (this.specified[i] = n);
      }
      for (let e in t)
        if (Object.prototype.hasOwnProperty.call(t, e)) {
          let i = t[e];
          i &&
            i.value !== O &&
            i.value !== b.inherit &&
            i.value !== b.unset &&
            i.value !== b.revert &&
            (this.specified[e] = i);
        }
    }
    createInstance(e) {
      return new Wi(e, this);
    }
  },
  un = class extends Hs {
    constructor(e, t) {
      super(e, t),
        p(this, "pageAreaDimension", null),
        p(this, "pageMarginBoxInstances", {});
    }
    applyCascadeAndInit(e, t) {
      let i = this.cascaded;
      for (let e in t)
        if (Object.prototype.hasOwnProperty.call(t, e))
          switch (e) {
            case "writing-mode":
            case "direction":
              i[e] = t[e];
          }
      super.applyCascadeAndInit(e, t);
    }
    initHorizontal() {
      let e = this.style;
      (e.left = ne),
        (e["margin-left"] = ne),
        (e["border-left-width"] = ne),
        (e["padding-left"] = ne),
        (e["padding-right"] = ne),
        (e["border-right-width"] = ne),
        (e["margin-right"] = ne),
        (e.right = ne);
    }
    initVertical() {
      let e = this.style;
      (e.top = ne),
        (e["margin-top"] = ne),
        (e["border-top-width"] = ne),
        (e["padding-top"] = ne),
        (e["padding-bottom"] = ne),
        (e["border-bottom-width"] = ne),
        (e["margin-bottom"] = ne),
        (e.bottom = ne);
    }
    setPageAreaDimension(e) {
      this.pageAreaDimension = e;
      let t = this.style;
      (t.width = new F(e.borderBoxWidth)),
        (t.height = new F(e.borderBoxHeight)),
        (t["padding-left"] = new F(e.marginLeft)),
        (t["padding-right"] = new F(e.marginRight)),
        (t["padding-top"] = new F(e.marginTop)),
        (t["padding-bottom"] = new F(e.marginBottom));
    }
    adjustPageLayout(e, t, i) {
      let n = t.marginBoxes,
        r = {
          start: this.pageAreaDimension.marginLeft,
          end: this.pageAreaDimension.marginRight,
          extent: this.pageAreaDimension.borderBoxWidth,
        },
        s = {
          start: this.pageAreaDimension.marginTop,
          end: this.pageAreaDimension.marginBottom,
          extent: this.pageAreaDimension.borderBoxHeight,
        };
      this.sizeMarginBoxesAlongVariableDimension(n.top, !0, r, e, i),
        this.sizeMarginBoxesAlongVariableDimension(n.bottom, !0, r, e, i),
        this.sizeMarginBoxesAlongVariableDimension(n.left, !1, s, e, i),
        this.sizeMarginBoxesAlongVariableDimension(n.right, !1, s, e, i);
    }
    sizeMarginBoxesAlongVariableDimension(e, t, i, n, r) {
      let s = "start",
        o = "center",
        a = "end",
        l = this.pageBox.scope,
        h = {},
        u = {},
        c = {};
      for (let i in e) {
        let n = bp[i];
        if (n) {
          let s = e[i],
            o = this.pageMarginBoxInstances[i],
            a = new Fo(s, o.style, t, l, r);
          (h[n.positionAlongVariableDimension] = s),
            (u[n.positionAlongVariableDimension] = o),
            (c[n.positionAlongVariableDimension] = a);
        }
      }
      let d = {
          start: i.start.evaluate(n),
          end: i.end.evaluate(n),
          extent: i.extent.evaluate(n),
        },
        p = this.getSizesOfMarginBoxesAlongVariableDimension(c, d.extent),
        f = !1,
        g = {};
      Object.keys(h).forEach((e) => {
        let s = e,
          o = te(l, u[s].style[t ? "max-width" : "max-height"], i.extent);
        if (o) {
          let e = o.evaluate(n);
          if (p[s] > e) {
            let i = (c[s] = new Xa(h[s], u[s].style, t, l, r, e));
            (g[s] = i.getOuterSize()), (f = !0);
          }
        }
      }),
        f &&
          ((p = this.getSizesOfMarginBoxesAlongVariableDimension(c, d.extent)),
          (f = !1),
          [s, o, a].forEach((e) => {
            var t;
            p[e] = null != (t = g[e]) ? t : p[e];
          }));
      let m = {};
      Object.keys(h).forEach((e) => {
        let s = e,
          o = te(l, u[s].style[t ? "min-width" : "min-height"], i.extent);
        if (o) {
          let e = o.evaluate(n);
          if (p[s] < e) {
            let i = (c[s] = new Xa(h[s], u[s].style, t, l, r, e));
            (m[s] = i.getOuterSize()), (f = !0);
          }
        }
      }),
        f &&
          ((p = this.getSizesOfMarginBoxesAlongVariableDimension(c, d.extent)),
          [s, o, a].forEach((e) => {
            p[e] = m[e] || p[e];
          }));
      let w = d.start + d.extent,
        b = d.start + (d.start + d.extent);
      [s, o, a].forEach((e) => {
        let i = p[e];
        if (null != i) {
          let n = h[e],
            r = 0;
          switch (e) {
            case s:
              r = t ? n.left : n.top;
              break;
            case o:
              r = (b - i) / 2;
              break;
            case a:
              r = w - i;
          }
          t
            ? n.setHorizontalPosition(
                r,
                i - n.getInsetLeft() - n.getInsetRight()
              )
            : n.setVerticalPosition(
                r,
                i - n.getInsetTop() - n.getInsetBottom()
              );
        }
      });
    }
    getSizesOfMarginBoxesAlongVariableDimension(e, t) {
      var i;
      let n = e.start,
        r = e.center,
        s = e.end,
        o = {};
      if (r) {
        let e = new op([n, s]),
          a = this.distributeAutoMarginBoxSizes(r, e, t);
        null != a.xSize && (o.center = a.xSize);
        let l = null != (i = a.xSize) ? i : r.getOuterSize(),
          h = Math.max((t - l) / 2, 0);
        n && (o.start = n.hasAutoSize() ? h : n.getOuterSize()),
          s && (o.end = s.hasAutoSize() ? h : s.getOuterSize());
      } else {
        let e = this.distributeAutoMarginBoxSizes(n, s, t);
        null != e.xSize && (o.start = e.xSize),
          null != e.ySize && (o.end = e.ySize);
      }
      return o;
    }
    distributeAutoMarginBoxSizes(e, t, i) {
      let n = { xSize: null, ySize: null };
      if (
        (e instanceof Fo && e.minMaxFitContent && (n.xSize = e.getOuterSize()),
        t instanceof Fo && t.minMaxFitContent && (n.ySize = t.getOuterSize()),
        e && t)
      )
        if (e.hasAutoSize() && t.hasAutoSize()) {
          let r = e.getOuterMaxContentSize(),
            s = t.getOuterMaxContentSize();
          if (r > 0 && s > 0) {
            let o = r + s;
            if (o < i) n.xSize = (i * r) / o;
            else {
              let s = e.getOuterMinContentSize(),
                a = s + t.getOuterMinContentSize();
              a < i
                ? (n.xSize = s + ((i - a) * (r - s)) / (o - a))
                : a > 0 && (n.xSize = (i * s) / a);
            }
            n.xSize > 0 && (n.ySize = i - n.xSize);
          } else r > 0 ? (n.xSize = i) : s > 0 && (n.ySize = i);
        } else
          e.hasAutoSize()
            ? (n.xSize = Math.max(i - t.getOuterSize(), 0))
            : t.hasAutoSize() && (n.ySize = Math.max(i - e.getOuterSize(), 0));
      else
        e
          ? e.hasAutoSize() && (n.xSize = i)
          : t && t.hasAutoSize() && (n.ySize = i);
      return n;
    }
    prepareContainer(e, t, i, n, r) {
      t.element.setAttribute("data-vivliostyle-page-box", !0),
        super.prepareContainer(e, t, i, n, r);
    }
  },
  Fo = class {
    constructor(e, t, i, n, r) {
      (this.container = e),
        (this.isHorizontal = i),
        (this.clientLayout = r),
        p(this, "hasAutoSize_"),
        p(this, "size", null),
        p(this, "minMaxFitContent", null);
      let s = t[i ? "width" : "height"];
      if (
        ((this.hasAutoSize_ = !s || s === b.auto || M(s)),
        (this.minMaxFitContent =
          s instanceof be &&
          ("min-content" === s.name ||
            "max-content" === s.name ||
            "fit-content" === s.name)
            ? s.name
            : null),
        this.minMaxFitContent)
      ) {
        let e = this.getSize();
        if (this.isHorizontal) {
          let t =
            e[
              "min-content" === this.minMaxFitContent
                ? "min-content width"
                : "max-content" === this.minMaxFitContent
                ? "max-content width"
                : "fit-content width"
            ];
          this.container.setHorizontalPosition(this.container.left, t);
        } else {
          let t =
            e[
              "min-content" === this.minMaxFitContent
                ? "min-content height"
                : "max-content" === this.minMaxFitContent
                ? "max-content height"
                : "fit-content height"
            ];
          this.container.setVerticalPosition(this.container.top, t);
        }
      }
    }
    hasAutoSize() {
      return this.hasAutoSize_;
    }
    getSize() {
      if (!this.size) {
        let e = this.isHorizontal
          ? ["max-content width", "min-content width", "fit-content width"]
          : ["max-content height", "min-content height", "fit-content height"];
        this.size = po(this.clientLayout, this.container.element, e);
      }
      return this.size;
    }
    getOuterMaxContentSize() {
      let e = this.getSize();
      return this.isHorizontal
        ? this.container.getInsetLeft() +
            e["max-content width"] +
            this.container.getInsetRight()
        : this.container.getInsetTop() +
            e["max-content height"] +
            this.container.getInsetBottom();
    }
    getOuterMinContentSize() {
      let e = this.getSize();
      return this.isHorizontal
        ? this.container.getInsetLeft() +
            e["min-content width"] +
            this.container.getInsetRight()
        : this.container.getInsetTop() +
            e["min-content height"] +
            this.container.getInsetBottom();
    }
    getOuterSize() {
      return this.isHorizontal
        ? this.container.getInsetLeft() +
            this.container.width +
            this.container.getInsetRight()
        : this.container.getInsetTop() +
            this.container.height +
            this.container.getInsetBottom();
    }
  },
  op = class {
    constructor(e) {
      this.params = e;
    }
    hasAutoSize() {
      return this.params.some((e) => {
        var t;
        return null != (t = null == e ? void 0 : e.hasAutoSize()) && t;
      });
    }
    getOuterMaxContentSize() {
      let e = this.params.map((e) => {
        var t;
        return null != (t = null == e ? void 0 : e.getOuterMaxContentSize())
          ? t
          : 0;
      });
      return Math.max.apply(null, e) * e.length;
    }
    getOuterMinContentSize() {
      let e = this.params.map((e) => {
        var t;
        return null != (t = null == e ? void 0 : e.getOuterMinContentSize())
          ? t
          : 0;
      });
      return Math.max.apply(null, e) * e.length;
    }
    getOuterSize() {
      let e = this.params.map((e) => {
        var t;
        return null != (t = null == e ? void 0 : e.getOuterSize()) ? t : 0;
      });
      return Math.max.apply(null, e) * e.length;
    }
  },
  Xa = class extends Fo {
    constructor(e, t, i, n, r, s) {
      super(e, t, i, n, r), p(this, "fixedSize"), (this.fixedSize = s);
    }
    hasAutoSize() {
      return !1;
    }
    getOuterMaxContentSize() {
      return this.getOuterSize();
    }
    getOuterMinContentSize() {
      return this.getOuterSize();
    }
    getOuterSize() {
      return this.isHorizontal
        ? this.container.getInsetLeft() +
            this.fixedSize +
            this.container.getInsetRight()
        : this.container.getInsetTop() +
            this.fixedSize +
            this.container.getInsetBottom();
    }
  },
  ip = class extends cn {
    constructor(e, t) {
      super(e, t),
        p(this, "borderBoxWidth", null),
        p(this, "borderBoxHeight", null),
        p(this, "contentBoxWidth", null),
        p(this, "contentBoxHeight", null),
        p(this, "marginTop", null),
        p(this, "marginRight", null),
        p(this, "marginBottom", null),
        p(this, "marginLeft", null);
    }
    applyCascadeAndInit(e, t) {
      for (let e in t) e.match(/^background-/) && (this.cascaded[e] = t[e]);
      super.applyCascadeAndInit(e, t),
        this.parentInstance.setPageAreaDimension({
          borderBoxWidth: this.borderBoxWidth,
          borderBoxHeight: this.borderBoxHeight,
          marginTop: this.marginTop,
          marginRight: this.marginRight,
          marginBottom: this.marginBottom,
          marginLeft: this.marginLeft,
        });
    }
    initHorizontal() {
      let e = this.resolvePageBoxDimensions({
        start: "left",
        end: "right",
        extent: "width",
      });
      (this.borderBoxWidth = e.borderBoxExtent),
        (this.contentBoxWidth = e.contentBoxExtent),
        (this.marginLeft = e.marginStart),
        (this.marginRight = e.marginEnd);
    }
    initVertical() {
      let e = this.resolvePageBoxDimensions({
        start: "top",
        end: "bottom",
        extent: "height",
      });
      (this.borderBoxHeight = e.borderBoxExtent),
        (this.contentBoxHeight = e.contentBoxExtent),
        (this.marginTop = e.marginStart),
        (this.marginBottom = e.marginEnd);
    }
    resolvePageBoxDimensions(e) {
      let t,
        i,
        n,
        r = this.style,
        s = this.pageBox.pageSize,
        o = this.pageBox.scope,
        a = e.start,
        l = e.end,
        h = e.extent,
        u = s[h].toExpr(o, null),
        c = te(o, r[h], u),
        d = te(o, r[`margin-${a}`], u),
        p = te(o, r[`margin-${l}`], u),
        f = ot(o, r[`padding-${a}`], u),
        g = ot(o, r[`padding-${l}`], u),
        m = $t(o, r[`border-${a}-width`], r[`border-${a}-style`], u),
        w = $t(o, r[`border-${l}-width`], r[`border-${l}-style`], u),
        b = H(o, H(o, m, f), H(o, w, g));
      return (
        c
          ? (this.borderBoxSizing
              ? ((i = c), (n = K(o, i, b)))
              : ((n = c), (i = H(o, n, b))),
            (t = K(o, u, i)),
            d || p
              ? d
                ? (p = K(o, t, d))
                : (d = K(o, t, p))
              : ((d = Js(o, t, new ie(o, 0.5))), (p = d)))
          : (d || (d = o.zero),
            p || (p = o.zero),
            (t = H(o, d, p)),
            (i = K(o, u, t)),
            (n = K(o, i, b)),
            (c = this.borderBoxSizing ? i : n)),
        (r[a] = new F(d)),
        (r[l] = new F(p)),
        (r[`margin-${a}`] = ne),
        (r[`margin-${l}`] = ne),
        (r[`border-${a}-width`] = new F(m)),
        (r[`border-${l}-width`] = new F(w)),
        (r[`padding-${a}`] = new F(f)),
        (r[`padding-${l}`] = new F(g)),
        (r[h] = new F(c)),
        (r[`max-${h}`] = new F(c)),
        {
          borderBoxExtent: i,
          contentBoxExtent: n,
          marginStart: d,
          marginEnd: p,
        }
      );
    }
    prepareContainer(e, t, i, n, r) {
      t.element.setAttribute("data-vivliostyle-page-area-container", !0),
        super.prepareContainer(e, t, i, n, r),
        w(t.element, "position", "relative"),
        w(t.element, "inset", ""),
        w(t.element, "display", "flow-root");
    }
  },
  Gi = class extends cn {
    constructor(e, t) {
      super(e, t);
    }
    applyCascadeAndInit(e, t) {
      for (let e in t) e.match(/^column.*$/) && (this.cascaded[e] = t[e]);
      super.applyCascadeAndInit(e, {});
    }
    initHorizontal() {
      let e = this.style,
        t = this.parentInstance.style,
        i = this.pageBox.scope;
      (e.left = t["padding-left"]),
        (e.width = new F(this.parentInstance.contentBoxWidth)),
        (e["margin-left"] = new F(new Qt(i, t.left.toExpr(i, null)))),
        (e["margin-right"] = new F(new Qt(i, t.right.toExpr(i, null)))),
        (e["border-left-width"] = t.left),
        (e["border-right-width"] = t.right),
        (e["border-left-style"] = b.solid),
        (e["border-right-style"] = b.solid),
        (e["border-left-color"] = b.transparent),
        (e["border-right-color"] = b.transparent);
    }
    initVertical() {
      let e = this.style,
        t = this.parentInstance.style,
        i = this.pageBox.scope;
      (e.top = t["padding-top"]),
        (e.height = new F(this.parentInstance.contentBoxHeight)),
        (e["margin-top"] = new F(new Qt(i, t.top.toExpr(i, null)))),
        (e["margin-bottom"] = new F(new Qt(i, t.bottom.toExpr(i, null)))),
        (e["border-top-width"] = t.top),
        (e["border-bottom-width"] = t.bottom),
        (e["border-top-style"] = b.solid),
        (e["border-bottom-style"] = b.solid),
        (e["border-top-color"] = b.transparent),
        (e["border-bottom-color"] = b.transparent);
    }
    prepareContainer(e, t, i, n, r) {
      t.element.setAttribute("data-vivliostyle-page-area", !0),
        (i.pageAreaElement = t.element),
        super.prepareContainer(e, t, i, n, r),
        (e.pageAreaWidth = parseFloat(i.pageAreaElement.style.width)),
        (e.pageAreaHeight = parseFloat(i.pageAreaElement.style.height)),
        w(t.element, "position", "relative"),
        w(t.element, "inset", ""),
        w(t.element, "display", "flow-root");
    }
  },
  Wi = class extends cn {
    constructor(e, t) {
      super(e, t),
        p(this, "boxInfo"),
        p(this, "suppressEmptyBoxGeneration", !0);
      let i = t.marginBoxName;
      (this.boxInfo = bp[i]), (e.pageMarginBoxInstances[i] = this);
    }
    prepareContainer(e, t, i, n, r) {
      t.element.setAttribute(
        "data-vivliostyle-page-margin-box",
        this.pageBox.marginBoxName
      ),
        this.applyVerticalAlign(e, t.element),
        super.prepareContainer(e, t, i, n, r);
    }
    applyVerticalAlign(e, t) {
      w(t, "display", "flex"),
        w(
          t,
          "flex-flow",
          this.vertical ? (this.rtl ? "row-reverse" : "row") : "column"
        );
      let i = this.getProp(e, "vertical-align"),
        n = null;
      i === L("middle")
        ? (n = "center")
        : i === L("top")
        ? (n = "flex-start")
        : i === L("bottom") && (n = "flex-end"),
        n && w(t, "justify-content", n);
      let r = this.getProp(e, "content");
      if (
        this.vertical ||
        r instanceof Oe ||
        (r instanceof F &&
          r.expr instanceof Z &&
          r.expr.str.startsWith("running-element-"))
      ) {
        let e = "center";
        (this.boxInfo.isInTopRow || this.boxInfo.isInBottomRow) &&
          (this.boxInfo.isInLeftColumn ||
          "end" === this.boxInfo.positionAlongVariableDimension
            ? (e = this.vertical || this.rtl ? "start" : "end")
            : (this.boxInfo.isInRightColumn ||
                "start" === this.boxInfo.positionAlongVariableDimension) &&
              (e = this.vertical || this.rtl ? "end" : "start")),
          w(t, "align-items", e);
      }
    }
    positionAlongVariableDimension(e, t) {
      let i = this.style,
        n = this.pageBox.scope,
        r = e.start,
        s = e.end,
        o = e.extent,
        a = "left" === r,
        l = a ? t.borderBoxWidth : t.borderBoxHeight,
        h = te(n, i[o], l),
        u = a ? t.marginLeft : t.marginTop;
      if ("start" === this.boxInfo.positionAlongVariableDimension)
        i[r] = new F(u);
      else if (h) {
        let e = ot(n, i[`margin-${r}`], l),
          t = ot(n, i[`margin-${s}`], l),
          o = ot(n, i[`padding-${r}`], l),
          a = ot(n, i[`padding-${s}`], l),
          c = $t(n, i[`border-${r}-width`], i[`border-${r}-style`], l),
          d = $t(n, i[`border-${s}-width`], i[`border-${s}-style`], l),
          p = H(
            n,
            h,
            H(
              n,
              this.borderBoxSizing ? n.zero : H(n, H(n, o, a), H(n, c, d)),
              H(n, e, t)
            )
          );
        switch (this.boxInfo.positionAlongVariableDimension) {
          case "center":
            i[r] = new F(H(n, u, Yo(n, K(n, l, p), new ie(n, 2))));
            break;
          case "end":
            i[r] = new F(K(n, H(n, u, l), p));
        }
      }
    }
    positionAndSizeAlongFixedDimension(e, t) {
      let i = this.style,
        n = this.pageBox.scope,
        r = this.borderBoxSizing,
        s = e.inside,
        o = e.outside,
        a = e.extent,
        l = t[`margin${o.charAt(0).toUpperCase()}${o.substring(1)}`],
        h = Qd(n, i[`margin-${s}`], l),
        u = Qd(n, i[`margin-${o}`], l),
        c = ot(n, i[`padding-${s}`], l),
        d = ot(n, i[`padding-${o}`], l),
        p = $t(n, i[`border-${s}-width`], i[`border-${s}-style`], l),
        f = $t(n, i[`border-${o}-width`], i[`border-${o}-style`], l),
        g = te(n, i[a], l),
        m = null;
      function w(e) {
        if (m) return m;
        m = {
          extent: g ? g.evaluate(e) : null,
          marginInside: h ? h.evaluate(e) : null,
          marginOutside: u ? u.evaluate(e) : null,
        };
        let t = l.evaluate(e),
          i = 0;
        return (
          r ||
            [p, c, d, f].forEach((t) => {
              t && (i += t.evaluate(e));
            }),
          (null === m.marginInside || null === m.marginOutside) &&
            i + m.extent + m.marginInside + m.marginOutside > t &&
            (null === m.marginInside && (m.marginInside = 0),
            null === m.marginOutside && (m.marginOutside = 0)),
          null !== m.extent &&
            null !== m.marginInside &&
            null !== m.marginOutside &&
            (m.marginOutside = null),
          null === m.extent
            ? ((m.extent = t - i - m.marginInside - m.marginOutside),
              null === m.marginInside && (m.marginInside = 0),
              null === m.marginOutside && (m.marginOutside = 0))
            : "number" == typeof m.extent &&
              (null === m.marginInside && "number" == typeof m.marginOutside
                ? (m.marginInside = t - i - m.extent - m.marginOutside)
                : "number" == typeof m.marginInside && null === m.marginOutside
                ? (m.marginOutside = t - i - m.extent - m.marginInside)
                : (m.marginInside = m.marginOutside = (t - i - m.extent) / 2)),
          m
        );
      }
      (i[a] = new F(
        new Z(
          n,
          function () {
            let e = w(this).extent;
            return null === e ? "auto" : e;
          },
          a
        )
      )),
        (i[`margin-${s}`] = new F(
          new Z(
            n,
            function () {
              let e = w(this).marginInside;
              return null === e ? "auto" : e;
            },
            `margin-${s}`
          )
        )),
        (i[`margin-${o}`] = new F(
          new Z(
            n,
            function () {
              let e = w(this).marginOutside;
              return null === e ? "auto" : e;
            },
            `margin-${o}`
          )
        )),
        "left" === s
          ? (i.left = new F(H(n, t.marginLeft, t.borderBoxWidth)))
          : "top" === s &&
            (i.top = new F(H(n, t.marginTop, t.borderBoxHeight)));
    }
    initHorizontal() {
      let e = this.parentInstance.pageAreaDimension;
      this.boxInfo.isInLeftColumn
        ? this.positionAndSizeAlongFixedDimension(
            { inside: "right", outside: "left", extent: "width" },
            e
          )
        : this.boxInfo.isInRightColumn
        ? this.positionAndSizeAlongFixedDimension(
            { inside: "left", outside: "right", extent: "width" },
            e
          )
        : this.positionAlongVariableDimension(
            { start: "left", end: "right", extent: "width" },
            e
          );
    }
    initVertical() {
      let e = this.parentInstance.pageAreaDimension;
      this.boxInfo.isInTopRow
        ? this.positionAndSizeAlongFixedDimension(
            { inside: "bottom", outside: "top", extent: "height" },
            e
          )
        : this.boxInfo.isInBottomRow
        ? this.positionAndSizeAlongFixedDimension(
            { inside: "top", outside: "bottom", extent: "height" },
            e
          )
        : this.positionAlongVariableDimension(
            { start: "top", end: "bottom", extent: "height" },
            e
          );
    }
    finishContainer(e, t, i, n, r, s, o) {
      super.finishContainer(e, t, i, n, r, s, o);
      let a = i.marginBoxes,
        l = this.pageBox.marginBoxName,
        h = this.boxInfo;
      h.isInLeftColumn || h.isInRightColumn
        ? !h.isInTopRow &&
          !h.isInBottomRow &&
          (h.isInLeftColumn
            ? (a.left[l] = t)
            : h.isInRightColumn && (a.right[l] = t))
        : h.isInTopRow
        ? (a.top[l] = t)
        : h.isInBottomRow && (a.bottom[l] = t);
      let u = this.parentInstance.pageAreaDimension,
        c = this.getProp(e, "width"),
        d =
          c instanceof be &&
          ("min-content" === c.name ||
            "max-content" === c.name ||
            "fit-content" === c.name)
            ? c.name
            : null,
        p = this.getPropAsNumber(e, "min-width"),
        f = this.getPropAsNumber(e, "max-width");
      if (d || p || f) {
        d && w(t.element, "width", d),
          p && w(t.element, "min-width", `${p}px`),
          f && w(t.element, "max-width", `${f}px`);
        let i = this.getProp(e, "margin-left"),
          n = this.getProp(e, "margin-right");
        if (h.isInLeftColumn) {
          let r = u.borderBoxWidth.evaluate(e) + u.marginRight.evaluate(e);
          w(t.element, "right", `${r}px`),
            (i === b.auto || n !== b.auto) &&
              w(t.element, "margin-left", "auto"),
            n === b.auto && w(t.element, "margin-right", "auto");
        } else
          h.isInRightColumn &&
            (w(t.element, "right", "0"),
            (n === b.auto || i !== b.auto) &&
              w(t.element, "margin-right", "auto"),
            i === b.auto && w(t.element, "margin-left", "auto"));
      }
      let g = this.getProp(e, "height"),
        m =
          g instanceof be &&
          ("min-content" === g.name ||
            "max-content" === g.name ||
            "fit-content" === g.name)
            ? g.name
            : null,
        v = this.getPropAsNumber(e, "min-height"),
        y = this.getPropAsNumber(e, "max-height");
      if (m || v || y) {
        m && w(t.element, "height", m),
          v && w(t.element, "min-height", `${v}px`),
          y && w(t.element, "max-height", `${y}px`);
        let i = this.getProp(e, "margin-top"),
          n = this.getProp(e, "margin-bottom");
        if (h.isInTopRow) {
          let r = u.borderBoxHeight.evaluate(e) + u.marginBottom.evaluate(e);
          w(t.element, "bottom", `${r}px`),
            (i === b.auto || n !== b.auto) &&
              w(t.element, "margin-top", "auto"),
            n === b.auto && w(t.element, "margin-bottom", "auto");
        } else
          h.isInBottomRow &&
            (w(t.element, "bottom", "0"),
            (n === b.auto || i !== b.auto) &&
              w(t.element, "margin-bottom", "auto"),
            i === b.auto && w(t.element, "margin-top", "auto"));
      }
    }
  },
  ja = class {
    constructor(e, t, i, n, r) {
      (this.cascadeInstance = e),
        (this.pageScope = t),
        (this.rootPageBoxInstance = i),
        (this.context = n),
        (this.docElementStyle = r),
        p(this, "pageMasterCache", {}),
        this.definePageProgression();
    }
    definePageProgression() {
      let e = this.pageScope,
        t = this.context,
        i = t.isVersoFirstPage,
        n = new ee(e, "page-number"),
        r = new Rn(e, new Zs(e, n, new ie(e, 2)), i ? e.one : e.zero);
      e.defineName("recto-page", new mt(e, r)),
        e.defineName("verso-page", r),
        "ltr" === (t.pageProgression || mp(this.docElementStyle))
          ? (e.defineName("left-page", r),
            e.defineName("right-page", new mt(e, r)))
          : (e.defineName("left-page", new mt(e, r)),
            e.defineName("right-page", r));
    }
    getCascadedPageStyle(e) {
      let t = {};
      return (
        this.cascadeInstance.pushRule([], e, t),
        this.cascadeInstance.popRule(),
        t
      );
    }
    getPageRulePageMaster(e, t) {
      let i = e.pageBox;
      if (0 === Object.keys(t).length) return i.resetScope(), e;
      let n = this.makeCacheKey(t, i),
        r = this.pageMasterCache[n];
      return (
        r ||
          ((r =
            i.pseudoName === Mi
              ? this.generatePageRuleMaster(t)
              : this.generateCascadedPageMaster(t, i)),
          (this.pageMasterCache[n] = r)),
        r.pageBox.resetScope(),
        r
      );
    }
    makeCacheKey(e, t) {
      var i;
      let n = this.makeCascadeValueObjectKey(e),
        r =
          (null == (i = this.context.currentLayoutPosition) ? void 0 : i.page) %
          2;
      return `${t.key}^${n}^${r}`;
    }
    makeCascadeValueObjectKey(e) {
      let t = [];
      for (let i in e)
        if (Object.prototype.hasOwnProperty.call(e, i)) {
          let n,
            r = e[i];
          (n =
            r instanceof _ ? `${r.value}` : this.makeCascadeValueObjectKey(r)),
            t.push(i + n + (r.priority || ""));
        }
      return t.sort().join("^");
    }
    generatePageRuleMaster(e) {
      let t = new _i(
        this.pageScope,
        this.rootPageBoxInstance.pageBox,
        e
      ).createInstance(this.rootPageBoxInstance);
      return (
        t.applyCascadeAndInit(this.cascadeInstance, this.docElementStyle),
        t.resolveAutoSizing(this.context),
        t
      );
    }
    generateCascadedPageMaster(e, t) {
      let i = t.clone({ pseudoName: qa }),
        n = i.specified,
        r = e.size;
      if (r && !M(r.value)) {
        let t = $i(e),
          i = r.priority;
        ln(n, "width", new _(t.width, i), this.context),
          ln(n, "height", new _(t.height, i), this.context);
      }
      ["counter-reset", "counter-increment"].forEach((t) => {
        n[t] && (e[t] = n[t]);
      });
      let s = i.createInstance(this.rootPageBoxInstance);
      return (
        s.applyCascadeAndInit(this.cascadeInstance, this.docElementStyle),
        s.resolveAutoSizing(this.context),
        s
      );
    }
  },
  rp = class extends Q {
    constructor(e) {
      super(), (this.pageType = e);
    }
    apply(e) {
      e.currentPageType === this.pageType && this.chained.apply(e);
    }
    getPriority() {
      return 3;
    }
    makePrimary(e) {
      return (
        this.chained &&
          e.insertInTable(e.pagetypes, this.pageType, this.chained),
        !0
      );
    }
  },
  ap = class extends Q {
    constructor(e) {
      super(), (this.scope = e);
    }
    apply(e) {
      1 === new ee(this.scope, "page-number").evaluate(e.context) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 2;
    }
  },
  lp = class extends Q {
    constructor(e) {
      super(), (this.scope = e);
    }
    apply(e) {
      new ee(this.scope, "blank-page").evaluate(e.context) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 2;
    }
  },
  cp = class extends Q {
    constructor(e) {
      super(), (this.scope = e);
    }
    apply(e) {
      new ee(this.scope, "left-page").evaluate(e.context) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 1;
    }
  },
  up = class extends Q {
    constructor(e) {
      super(), (this.scope = e);
    }
    apply(e) {
      new ee(this.scope, "right-page").evaluate(e.context) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 1;
    }
  },
  dp = class extends Q {
    constructor(e) {
      super(), (this.scope = e);
    }
    apply(e) {
      new ee(this.scope, "recto-page").evaluate(e.context) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 1;
    }
  },
  pp = class extends Q {
    constructor(e) {
      super(), (this.scope = e);
    }
    apply(e) {
      new ee(this.scope, "verso-page").evaluate(e.context) &&
        this.chained.apply(e);
    }
    getPriority() {
      return 1;
    }
  },
  hp = class extends Kn {
    constructor(e, t, i) {
      super(t, i), (this.scope = e), (this.a = t), (this.b = i);
    }
    apply(e) {
      let t = e.context,
        i = t.layoutPositionAtPageStart.page;
      t.blankPageAtStart && i--,
        i && this.matchANPlusB(i) && this.chained.apply(e);
    }
    getPriority() {
      return 2;
    }
  },
  fp = class extends Rs {
    constructor(e, t) {
      super(e, t, null, null, null);
    }
    apply(e) {
      hb(e.context, e.currentStyle, this.style, this.specificity, e);
    }
  };
function hb(e, t, i, n, r) {
  yi(e, t, i, n, null, null, null);
  let s = i[zi];
  if (s) {
    let i = wo(t, zi);
    for (let t in s)
      if (s.hasOwnProperty(t)) {
        let r = i[t];
        r || ((r = {}), (i[t] = r)), yi(e, r, s[t], n, null, null, null);
      }
  }
}
var Ya = class extends Nn {
    constructor(e, t, i, n, r) {
      super(e, t, null == i ? void 0 : i.condition, i, null, n, !1),
        (this.pageProps = r),
        p(this, "currentPageSelectors", []),
        p(this, "currentNamedPageSelector", ""),
        p(this, "currentPseudoPageClassSelectors", []);
    }
    startPageRule() {
      this.startSelectorRule();
    }
    tagSelector(e, t) {
      (this.currentNamedPageSelector = t),
        t && (this.chain.push(new rp(t)), (this.specificity += 65536));
    }
    pseudoclassSelector(e, t) {
      if (((e = e.toLowerCase()), t))
        if ("nth" === e) {
          let [i, n] = t;
          this.currentPseudoPageClassSelectors.push(
            `:${e}(${i}n${n < 0 ? n : "+" + n})`
          ),
            this.chain.push(new hp(this.scope, i, n)),
            (this.specificity += 256);
        } else
          this.reportAndSkip(`E_INVALID_PAGE_SELECTOR :${e}(${t.join("")})`);
      else
        switch ((this.currentPseudoPageClassSelectors.push(`:${e}`), e)) {
          case "first":
            this.chain.push(new ap(this.scope)), (this.specificity += 256);
            break;
          case "blank":
            this.chain.push(new lp(this.scope)), (this.specificity += 256);
            break;
          case "left":
            this.chain.push(new cp(this.scope)), (this.specificity += 1);
            break;
          case "right":
            this.chain.push(new up(this.scope)), (this.specificity += 1);
            break;
          case "recto":
            this.chain.push(new dp(this.scope)), (this.specificity += 1);
            break;
          case "verso":
            this.chain.push(new pp(this.scope)), (this.specificity += 1);
            break;
          default:
            this.reportAndSkip(`E_INVALID_PAGE_SELECTOR :${e}`);
        }
    }
    finishSelector() {
      let e;
      (e =
        this.currentNamedPageSelector ||
        this.currentPseudoPageClassSelectors.length
          ? [this.currentNamedPageSelector].concat(
              this.currentPseudoPageClassSelectors.sort()
            )
          : null),
        this.currentPageSelectors.push({
          selectors: e,
          specificity: this.specificity,
        }),
        (this.currentNamedPageSelector = ""),
        (this.currentPseudoPageClassSelectors = []);
    }
    nextSelector() {
      this.finishSelector(), super.nextSelector();
    }
    startRuleBody() {
      this.finishSelector(), super.startRuleBody();
    }
    simpleProperty(e, t, i) {
      if (
        ("bleed" === e || "marks" === e) &&
        !this.currentPageSelectors.some((e) => null === e.selectors)
      )
        return;
      super.simpleProperty(e, t, i);
      let n = he(this.elementStyle, e),
        r = this.pageProps;
      if ("bleed" === e || "marks" === e)
        r[""] || (r[""] = {}),
          Object.keys(r).forEach((t) => {
            En(r[t], e, n);
          });
      else if ("size" === e) {
        let t = r[""];
        this.currentPageSelectors.forEach((i) => {
          let s = new _(n.value, n.priority + i.specificity),
            o = i.selectors ? i.selectors.join("") : "",
            a = r[o];
          a
            ? ln(a, e, s)
            : ((a = r[o] = {}),
              En(a, e, s),
              t &&
                ["bleed", "marks"].forEach((e) => {
                  t[e] && En(a, e, t[e]);
                }));
        });
      }
    }
    insertNonPrimary(e) {
      this.cascade.insertInTable(this.cascade.pagetypes, "*", e);
    }
    makeApplyRuleAction(e) {
      return new fp(this.elementStyle, e);
    }
    startPageMarginBoxRule(e) {
      let t = wo(this.elementStyle, zi),
        i = t[e];
      i || ((i = {}), (t[e] = i));
      let n = new gp(this.scope, this.owner, this.validatorSet, i);
      this.owner.pushHandler(n);
    }
  },
  gp = class extends nn {
    constructor(e, t, i, n) {
      super(e, t, !1), (this.validatorSet = i), (this.boxStyle = n);
    }
    property(e, t, i) {
      this.validatorSet.validatePropertyAndHandleShorthand(e, t, i, this);
    }
    invalidPropertyValue(e, t) {
      this.report(`E_INVALID_PROPERTY_VALUE ${e}: ${t.toString()}`);
    }
    unknownProperty(e, t) {
      this.report(`E_INVALID_PROPERTY ${e}: ${t.toString()}`);
    }
    simpleProperty(e, t, i) {
      let n = i ? this.getImportantSpecificity() : this.getBaseSpecificity(),
        r = new _(t, n);
      En(this.boxStyle, e, r);
    }
  },
  Xt = !0;
function Cg(e) {
  Xt = e;
}
var zs = [];
function mg(e, t) {
  return (
    e === t ||
    (e.src || t.src ? e.src === t.src : e.textContent === t.textContent)
  );
}
function bg(e) {
  let t = Array.from(
    e.querySelectorAll("body > :not(script):not(link):not(style) ~ script")
  );
  return Array.from(e.querySelectorAll("head > script, body > script")).filter(
    (e) => !t.includes(e)
  );
}
function Ka(e, t, i) {
  var n, r;
  if (!Xt) return T(!1);
  if (
    (null == i || !i.inHead) &&
    (null == i || !i.atEnd) &&
    bg(e.ownerDocument).includes(e)
  )
    return T(!1);
  let s = e.textContent,
    o = e.src,
    a = "module" === e.type,
    l = (a || o) && e.async,
    h = (a && !l) || (o && e.defer),
    u =
      !(null != i && i.atEnd) &&
      ((null == i ? void 0 : i.forceDefer) || h || l);
  if ((xp(t) || (t.onload = null), u))
    return zs.some((t) => mg(t, e)) || zs.push(e), T(!0);
  if (o.includes("/mathjax")) {
    let e = t.document.head.querySelector(
      "script[src*='MathJax.js']:not([data-vivliostyle-scripting])"
    );
    if (e)
      if (o.includes("/mathjax@3"))
        t.document.head.removeChild(e),
          null != (r = null == (n = t.MathJax) ? void 0 : n.version) &&
            r.startsWith("2.") &&
            delete t.MathJax;
      else if (o.includes("/MathJax.js")) return T(!0);
  }
  for (let i of t.document.head.getElementsByTagName("script"))
    i.hasAttribute("data-vivliostyle-scripting") &&
      mg(i, e) &&
      t.document.head.removeChild(i);
  let c = t.document.createElement("script");
  (c.textContent = s),
    o && (c.src = o),
    (c.async = l),
    (c.defer = h),
    c.setAttribute("data-vivliostyle-scripting", "true");
  for (let t of e.attributes)
    ["src", "async", "defer"].includes(t.name) ||
      c.setAttribute(t.name, t.value);
  if ((V.debug("script:", o), o)) {
    let e = On(c);
    return t.document.head.appendChild(c), Bn([e]);
  }
  return t.document.head.appendChild(c), T(!0);
}
function gb(e, t) {
  var i;
  let n = {},
    r = (e) => {
      var t;
      let i = null == (t = e["font-family"]) ? void 0 : t.value;
      if (i)
        if (i.values) for (let e of i.values) n[e.stringValue()] = !0;
        else n[i.stringValue()] = !0;
      let s = e._marginBoxes;
      if (s) for (let e of Object.values(s)) r(e);
    },
    s = (e) => {
      if (e instanceof Rs) r(e.style);
      else if (e instanceof Sn || Array.isArray(e))
        for (let t of Object.values(e)) s(t);
    };
  for (let e of Object.values(t.cascade.code))
    for (let t of Object.values(null != e ? e : {})) s(t);
  for (let t of e.querySelectorAll("[style]"))
    null != (i = t.style) && i.fontFamily && (n[t.style.fontFamily] = !0);
  return Object.keys(n).join(",");
}
function mb(e, t, i) {
  var n;
  let r =
    null != (n = t.document.querySelector("[data-vivliostyle-textcontent]"))
      ? n
      : t.document.createElement("div");
  return (
    (r.style.position = "fixed"),
    (r.style.fontSize = "0"),
    r.setAttribute("data-vivliostyle-textcontent", "true"),
    r.setAttribute("aria-hidden", "true"),
    (r.style.fontFamily = gb(e, i)),
    (r.textContent = e.documentElement.textContent),
    t.document.body.appendChild(r),
    r
  );
}
function xg(e, t, i) {
  if (!Xt) return T(!1);
  let n = bg(e);
  if (0 === n.length) return T(!1);
  let r = n.some((e) => !(e.async || e.defer || "module" === e.type)),
    s = r ? mb(e, t, i) : null,
    o = t.document.fonts,
    a = t.$,
    l = !1,
    h = A("loadScripts");
  return (
    h
      .loop(() => {
        if (0 === n.length)
          return r
            ? h.sleep(20).thenAsync(() => {
                var e, i;
                return "loading" === o.status ||
                  t.document.documentElement.classList.contains("wf-loading") ||
                  (null != (e = t.FontJSON) &&
                    e.Font &&
                    null != (i = t.ret) &&
                    i.readyState &&
                    t.ret.readyState < 4)
                  ? T(!0)
                  : T(!1);
              })
            : T(!1);
        let e = n.shift();
        return Ka(e, t, { inHead: !0, forceDefer: l }).thenAsync(
          () => (
            !l && t.$ !== a && (zs.push(e), (l = !0)),
            0 === n.length &&
              r &&
              (V.debug("dispatchEvent: DOMContentLoaded (document)"),
              t.document.dispatchEvent(new Event("DOMContentLoaded"))),
            T(!0)
          )
        );
      })
      .then(() => {
        s && s.remove(), h.finish(!0);
      }),
    h.result()
  );
}
function yg(e) {
  if (!Xt) return T(!1);
  let t = A("loadScripts");
  return (
    t
      .loop(() =>
        0 === zs.length
          ? T(!1)
          : Ka(zs.shift(), e, { atEnd: !0 }).thenReturn(zs.length > 0)
      )
      .then(() => {
        V.debug("dispatchEvent: DOMContentLoaded (window)"),
          e.dispatchEvent(new Event("DOMContentLoaded")),
          V.debug("dispatchEvent: load (window)"),
          e.dispatchEvent(new Event("load")),
          t.finish(!0);
      }),
    t.result()
  );
}
function xp(e) {
  return (
    !!Xt &&
    (zs.length > 0 ||
      !!e.document.head.querySelector("script[data-vivliostyle-scripting]"))
  );
}
var Eg = (e, t, i) =>
    e
      .replace(
        /[uU][rR][lL]\(\s*"((\\([^0-9a-fA-F]+|[0-9a-fA-F]+\s*)|[^"\r\n])+)"/gm,
        (e, n) => `url("${i.transformURL(n, t)}"`
      )
      .replace(
        /[uU][rR][lL]\(\s*'((\\([^0-9a-fA-F]+|[0-9a-fA-F]+\s*)|[^'\r\n])+)'/gm,
        (e, n) => `url('${i.transformURL(n, t)}'`
      )
      .replace(
        /[uU][rR][lL]\(\s*((\\([^0-9a-fA-F]+|[0-9a-fA-F]+\s*)|[^"'\r\n\)\s])+)/gm,
        (e, n) => `url(${i.transformURL(n, t)}`
      ),
  bb = {};
function xb(e) {
  e.addEventListener(
    "load",
    () => {
      e.contentWindow.navigator.epubReadingSystem = {
        name: "adapt",
        version: "0.1",
        layoutStyle: "paginated",
        hasFeature: function (e, t) {
          return "mouse-events" === e;
        },
      };
    },
    !1
  );
}
var Xi = class e extends kn {
  constructor(e, t, i, n, r, s, o, a, l, h, u, c, d) {
    super(),
      (this.flowName = e),
      (this.context = t),
      (this.viewport = i),
      (this.styler = n),
      (this.regionIds = r),
      (this.xmldoc = s),
      (this.docFaces = o),
      (this.footnoteStyle = a),
      (this.stylerProducer = l),
      (this.page = h),
      (this.customRenderer = u),
      (this.fallbackMap = c),
      (this.documentURLTransformer = d),
      p(this, "document"),
      p(this, "exprContentListener"),
      p(this, "nodeContext", null),
      p(this, "viewRoot", null),
      p(this, "isFootnote", !1),
      p(this, "sourceNode", null),
      p(this, "offsetInNode", 0),
      p(this, "viewNode", null),
      (this.document = i.document),
      (this.exprContentListener = n.counterListener.getExprContentListener());
  }
  clone() {
    return new e(
      this.flowName,
      this.context,
      this.viewport,
      this.styler,
      this.regionIds,
      this.xmldoc,
      this.docFaces,
      this.footnoteStyle,
      this.stylerProducer,
      this.page,
      this.customRenderer,
      this.fallbackMap,
      this.documentURLTransformer
    );
  }
  createPseudoelementShadow(e, t, i, n, r, s, o, a) {
    let l = this.getPseudoMap(
      i,
      this.regionIds,
      this.isFootnote,
      this.nodeContext,
      s
    );
    if (!l) return a;
    let h = [],
      u = ko.createElementNS("http://www.pyroxy.com/ns/shadow", "root"),
      c = u;
    for (let i of Bf) {
      let s;
      if (i) {
        if (!l[i] || ("footnote-marker" == i && (!t || !this.isFootnote)))
          continue;
        if (i.match(/^first-/)) {
          let t = n.display;
          if (!t || t === b.inline) continue;
          let i = e.firstElementChild;
          if (i && de(i.previousSibling)) {
            let e = r.getStyle(i, !1);
            if (e) {
              let t = he(e, "display");
              if (null != t && t.value && t.value !== b.inline) continue;
            }
          }
        }
        if ("before" === i || "after" === i) {
          let e = l[i].content;
          if (!e || !St(e.value)) continue;
        }
        h.push(i),
          (s = ko.createElementNS("http://www.w3.org/1999/xhtml", "span")),
          Ni(s, i);
      } else
        s = ko.createElementNS("http://www.pyroxy.com/ns/shadow", "content");
      c.appendChild(s), i.match(/^first-/) && (c = s);
    }
    if (!h.length) return a;
    let d = new Si(e, i, r, s, this.exprContentListener);
    return new Ns(e, u, null, o, a, Et.ROOTLESS, d);
  }
  getPseudoMap(e, t, i, n, r) {
    let s = Tt(e, "_pseudos");
    if (!s) return null;
    let o = {};
    for (let e in s) {
      let n = (o[e] = {});
      Po(n, s[e], r),
        ma(n, r, s[e]),
        Ku(s[e], t, i, (e, t) => {
          Po(n, t, r),
            qu(t, (e) => {
              Po(n, e, r);
            });
        });
    }
    return o;
  }
  createRefShadow(e, t, i, n, r) {
    let s = A("createRefShadow");
    return (
      this.xmldoc.store.load(e).then((o) => {
        let a = o;
        if (a) {
          let s = a.getElement(e);
          if (s) {
            let e = this.stylerProducer.getStylerForDoc(a);
            r = new Ns(i, s, a, n, r, t, e);
          }
        }
        s.finish(r);
      }),
      s.result()
    );
  }
  createShadows(e, t, i, n, r, s, o) {
    let a,
      l = A("createShadows"),
      h = n.template;
    if (h instanceof Oe || h === b.footnote) {
      let t = h instanceof Oe ? h.url : J("user-agent.xml#footnote", qt);
      a = this.createRefShadow(t, Et.ROOTLESS, e, o, null);
    } else a = T(null);
    return (
      a.then((a) => {
        let h = null;
        if (
          "http://www.pyroxy.com/ns/shadow" == e.namespaceURI &&
          "include" == e.localName
        ) {
          let t = e.getAttribute("href"),
            i = null;
          t
            ? (i = o ? o.xmldoc : this.xmldoc)
            : o &&
              ((t =
                "http://www.w3.org/1999/xhtml" == o.owner.namespaceURI
                  ? o.owner.getAttribute("href")
                  : o.owner.getAttributeNS(
                      "http://www.w3.org/1999/xlink",
                      "href"
                    )),
              (i = o.parentShadow ? o.parentShadow.xmldoc : this.xmldoc)),
            t &&
              ((t = J(t, i.url)),
              (h = this.createRefShadow(t, Et.ROOTED, e, o, a)));
        }
        null == h && (h = T(a));
        let u = null;
        h.then((a) => {
          if (n.display === b.table_cell) {
            let t = J("user-agent.xml#table-cell", qt);
            u = this.createRefShadow(t, Et.ROOTLESS, e, o, a);
          } else u = T(a);
          u.then((a) => {
            (a = this.createPseudoelementShadow(e, t, i, n, r, s, o, a)),
              l.finish(a);
          });
        });
      }),
      l.result()
    );
  }
  setViewRoot(e, t) {
    (this.viewRoot = e), (this.isFootnote = t);
  }
  computeStyle(e, t, i, n) {
    var r;
    let s = this.context,
      o = Sa(i, s, this.regionIds, this.isFootnote, this.nodeContext),
      a = !(null != (r = this.nodeContext) && r.parent);
    a &&
      (!o["writing-mode"] &&
        this.styler.rootStyle["writing-mode"] &&
        (o["writing-mode"] = this.styler.rootStyle["writing-mode"]),
      !o.direction &&
        this.styler.rootStyle.direction &&
        (o.direction = this.styler.rootStyle.direction));
    let l = e;
    e = ya(o, s, e);
    let h = !a && e !== l;
    t = Ea(o, s, t);
    let u = !!new ee(s.style.pageScope, "left-page").evaluate(s);
    if (
      (Na(o, n, e, t, u, (t, i) => {
        let n;
        h &&
          (e
            ? /^(min-|max-)?(height|inline-size)$/.test(t) &&
              (n = s.pageAreaHeight)
            : /^(min-|max-)?(width|inline-size)$/.test(t) &&
              (n = s.pageAreaWidth));
        let r = i.evaluate(s, t, n, e);
        return "font-family" == t && (r = this.docFaces.filterFontFamily(r)), r;
      }),
      h)
    ) {
      let t = n[e ? "height" : "width"];
      (!t || t === b.auto) && (n[e ? "height" : "width"] = b.max_content);
    }
    if (ci(n.position))
      return (n.position = b.fixed), (n.visibility = b.hidden), e;
    let c = n.position,
      d = n.float,
      p = Wc(
        n.display ||
          ("http://www.w3.org/1999/xhtml" === this.sourceNode.namespaceURI
            ? b.inline
            : void 0),
        c,
        d,
        this.sourceNode === this.xmldoc.root
      );
    return (
      ["display", "position", "float"].forEach((e) => {
        p[e] && (n[e] = p[e]);
      }),
      e
    );
  }
  inheritFromSourceParent(e) {
    let t = this.nodeContext.sourceNode,
      i = [],
      n = null,
      r = this.nodeContext.shadowContext,
      s = -1;
    for (; t && 1 == t.nodeType; ) {
      let e = r && r.root == t;
      if (!e || r.type == Et.ROOTLESS) {
        let e = (r ? r.styler : this.styler).getStyle(t, !1);
        i.push(e), (n = n || _o(t));
      }
      e ? ((t = r.owner), (r = r.parentShadow)) : ((t = t.parentNode), s++);
    }
    let o = 0 === s,
      a = this.context.queryUnitSize("em", o),
      l = { "font-size": new _(new P(a, "px"), 0) },
      h = new da(l, this.context);
    for (let e = i.length - 1; e >= 0; --e) {
      let t,
        n,
        r = i[e],
        s = [];
      for (let e in r) Zn(e) && s.push(e);
      s.sort(Wl);
      for (let o of s) {
        h.setPropName(o);
        let s = he(r, o),
          a = s;
        M(s.value) ||
          ("font-size" === o &&
          e === i.length - 1 &&
          this.context.isRelativeRootFontSize &&
          this.context.rootFontSize
            ? (a = new _(new P(this.context.rootFontSize, "px"), s.priority))
            : "line-height" === o &&
              e === i.length - 1 &&
              this.context.rootLineHeight &&
              s.value instanceof P &&
              ("lh" === s.value.unit || "rlh" === s.value.unit)
            ? (a = new _(new P(this.context.rootLineHeight, "px"), s.priority))
            : 0 === e && s.value instanceof P && "lh" === s.value.unit
            ? (a = new _(
                new P(s.value.num * this.getLineHeightUnitSize(o, t, n), "px"),
                s.priority
              ))
            : bt(o) || (a = s.filterValue(h)),
          "font-size" === o
            ? (t = a.value)
            : "line-height" === o && (n = a.value),
          (l[o] = a));
      }
    }
    for (let t in e) Zn(t) || (l[t] = e[t]);
    return { lang: n, elementStyle: l };
  }
  resolveURL(e) {
    return (e = J(e, this.xmldoc.url)), this.fallbackMap[e] || e;
  }
  inheritLangAttribute() {
    this.nodeContext.lang =
      _o(this.nodeContext.sourceNode) ||
      (this.nodeContext.parent && this.nodeContext.parent.lang) ||
      this.nodeContext.lang;
  }
  transferPolyfilledInheritedProps(e) {
    let t = gf().filter((t) => e[t]);
    if (t.length) {
      let i = this.nodeContext.inheritedProps;
      if (this.nodeContext.parent) {
        i = this.nodeContext.inheritedProps = {};
        for (let e in this.nodeContext.parent.inheritedProps)
          i[e] = this.nodeContext.parent.inheritedProps[e];
      }
      t.forEach((t) => {
        let n = e[t];
        if (n) {
          if (M(n)) n === b.initial && (i[t] = void 0);
          else if (n instanceof ut) i[t] = n.num;
          else if (n instanceof be) i[t] = n.name;
          else if (n instanceof P) {
            let e = n;
            switch (e.unit) {
              case "dpi":
              case "dpcm":
              case "dppx":
                i[t] = e.num * Y[e.unit];
                break;
              default:
                i[t] = n;
            }
          } else i[t] = n;
          ["widows", "orphans"].includes(t) || delete e[t];
        }
      });
    }
  }
  resolveFormattingContext(e, t, i, n, r, s) {
    let o = Ge("RESOLVE_FORMATTING_CONTEXT");
    for (let a = 0; a < o.length; a++) {
      let l = o[a](e, t, i, n, r, s);
      if (l) return void (e.formattingContext = l);
    }
  }
  createElementView(e, t) {
    var i, n, r, s;
    let o = !0,
      a = A("createElementView"),
      l = this.sourceNode,
      h = this.nodeContext.shadowContext
        ? this.nodeContext.shadowContext.styler
        : this.styler,
      u = h.getStyle(l, !1);
    if (!this.nodeContext.shadowContext) {
      let e = this.xmldoc.getElementOffset(l);
      qn.registerFragmentIndex(e, this.nodeContext.fragmentIndex, 0);
    }
    let c = {};
    if (!this.nodeContext.parent) {
      let e = this.inheritFromSourceParent(u);
      (u = e.elementStyle), (this.nodeContext.lang = e.lang);
    }
    let d = u.position,
      p = u.float,
      f = u["float-reference"],
      g =
        p && !M(p.value) && p.value !== b.none && f && !M(f.value)
          ? Zh(f.value.toString())
          : null;
    if (
      this.nodeContext.parent &&
      (ci(null == d ? void 0 : d.value) || (g && Gn(g)))
    ) {
      let e = this.inheritFromSourceParent(u);
      (u = e.elementStyle), (this.nodeContext.lang = e.lang);
    }
    (this.nodeContext.vertical = this.computeStyle(
      this.nodeContext.vertical,
      "rtl" === this.nodeContext.direction,
      u,
      c
    )),
      g && Gn(g) && c.display === b.block && (c.display = b.flow_root),
      h.processContent(
        l,
        c,
        null != (n = this.nodeContext.viewNode)
          ? n
          : null == (i = this.nodeContext.parent)
          ? void 0
          : i.viewNode
      ),
      this.transferPolyfilledInheritedProps(c),
      this.inheritLangAttribute(),
      c.direction && (this.nodeContext.direction = c.direction.toString());
    let m = c["flow-into"];
    if (m && m.toString() != this.flowName) return a.finish(!1), a.result();
    if (
      Xt &&
      "script" === l.localName &&
      "http://www.w3.org/1999/xhtml" === l.namespaceURI
    )
      return Ka(l, this.viewport.window).thenFinish(a), a.result();
    let v = c.display;
    if (
      (M(v) &&
        (v =
          v === b.initial || v === b.unset
            ? b.inline
            : v === b.inherit
            ? (null == (r = this.nodeContext.parent) ? void 0 : r.display) &&
              L(null == (s = this.nodeContext.parent) ? void 0 : s.display)
            : null),
      v === b.none)
    )
      return a.finish(!1), a.result();
    let y = null == this.nodeContext.parent;
    return (
      (this.nodeContext.flexContainer = v === b.flex),
      this.createShadows(
        l,
        y,
        u,
        c,
        h,
        this.context,
        this.nodeContext.shadowContext
      ).then((t) => {
        var i, n;
        this.nodeContext.nodeShadow = t;
        let r = c.position,
          s = c.float,
          d = c.clear,
          p = this.nodeContext.vertical ? b.vertical_rl : b.horizontal_tb,
          f = this.nodeContext.parent
            ? this.nodeContext.parent.vertical
              ? b.vertical_rl
              : b.horizontal_tb
            : p,
          m = Jh(l),
          x = c["column-count"],
          S = c["column-width"],
          C = (x && x !== b.auto && !M(x)) || (S && S !== b.auto && !M(S));
        (this.nodeContext.establishesBFC = tf(
          v,
          r,
          s,
          c.overflow,
          p,
          f,
          C || m
        )),
          (this.nodeContext.containingBlockForAbsolute = nf(r)),
          this.nodeContext.isInsideBFC() &&
            s !== b.footnote &&
            (!g || !Gn(g)) &&
            ((s = null), (d = null));
        let E =
          s instanceof q ||
          s === b.left ||
          s === b.right ||
          s === b.top ||
          s === b.bottom ||
          s === b.inline_start ||
          s === b.inline_end ||
          s === b.block_start ||
          s === b.block_end ||
          s === b.snap_block ||
          s === b.snap_inline ||
          s === b.inside ||
          s === b.outside ||
          s === b.footnote;
        s &&
          (delete c.float,
          s === b.footnote &&
            (this.isFootnote
              ? ((E = !1), (c.display = b.block))
              : (c.display = b.inline))),
          d &&
            (d === b.inherit &&
              this.nodeContext.parent &&
              this.nodeContext.parent.clearSide &&
              (d = L(this.nodeContext.parent.clearSide)),
            (d === b.left ||
              d === b.right ||
              d === b.top ||
              d === b.bottom ||
              d === b.inline_start ||
              d === b.inline_end ||
              d === b.block_start ||
              d === b.block_end ||
              d === b.inside ||
              d === b.outside ||
              d === b.both ||
              d === b.all ||
              d === b.same ||
              d === b.column ||
              d === b.region ||
              d === b.page) &&
              (delete c.clear,
              c.display &&
                c.display != b.inline &&
                (this.nodeContext.clearSide = d.toString())));
        let k = v === b.list_item && c["ua-list-item-count"],
          N = c["break-inside"];
        (E || (N && !M(N) && N !== b.auto)) && this.nodeContext.breakPenalty++,
          v && v !== b.inline && Gt(v) && this.nodeContext.breakPenalty++,
          (this.nodeContext.inline = (!E && !v) || Gt(v) || ia(v)),
          (this.nodeContext.display = v ? v.toString() : "inline"),
          (this.nodeContext.floatSide = E ? s.toString() : null),
          (this.nodeContext.floatReference = g || le.INLINE);
        let I = c["float-min-wrap-block"];
        this.nodeContext.floatMinWrapBlock = I && !M(I) ? I : null;
        let A = this.isInsideNonRootMultiColumn(),
          R = c["column-span"];
        if (
          ((this.nodeContext.columnSpan = A || !R || M(R) ? null : R),
          !this.nodeContext.inline)
        ) {
          let e = c["break-after"];
          e &&
            !M(e) &&
            (!A || e !== b.column) &&
            ((this.nodeContext.breakAfter = e.toString()),
            Wr[this.nodeContext.breakAfter] && (c["break-after"] = b.column));
          let t = c["break-before"];
          t &&
            !M(t) &&
            (!A || t !== b.column) &&
            ((this.nodeContext.breakBefore = t.toString()),
            Wr[this.nodeContext.breakBefore] && (c["break-before"] = b.column),
            1 !== this.nodeContext.fragmentIndex &&
              (this.nodeContext.breakBefore = null));
          let i = c.page,
            n = i && !M(i) && i.toString();
          !n &&
            !this.nodeContext.parent &&
            (this.nodeContext.shadowContext ||
              v === b.table_header_group ||
              v === b.table_footer_group) &&
            (n = this.styler.cascade.currentPageType),
            n && "auto" !== n.toLowerCase()
              ? (this.nodeContext.pageType = n)
              : (n = this.nodeContext.pageType),
            this.styler.cascade.currentPageType !== n &&
              (n || c.position !== b.fixed) &&
              (1 === this.nodeContext.fragmentIndex &&
                !Mn(this.nodeContext.breakBefore) &&
                (this.nodeContext.breakBefore = "page"),
              n !== this.styler.cascade.previousPageType &&
                (this.styler.cascade.previousPageType =
                  this.styler.cascade.currentPageType),
              (this.styler.cascade.currentPageType = n));
        }
        (this.nodeContext.verticalAlign =
          (c["vertical-align"] && c["vertical-align"].toString()) ||
          "baseline"),
          (this.nodeContext.captionSide =
            (c["caption-side"] && c["caption-side"].toString()) || "top");
        let F = c["border-collapse"];
        if (!F || F === L("separate")) {
          let e,
            t,
            i = c["border-spacing"];
          i &&
            (i instanceof q
              ? ((e = i.values[0]), (t = i.values[1]))
              : (e = t = i),
            e.isNumeric() &&
              (this.nodeContext.inlineBorderSpacing = ke(e, this.context)),
            t.isNumeric() &&
              (this.nodeContext.blockBorderSpacing = ke(t, this.context)));
        }
        let O = c["footnote-policy"];
        this.nodeContext.footnotePolicy = O && !M(O) ? O : null;
        let B = c["x-first-pseudo"];
        if (B) {
          let e = this.nodeContext.parent
            ? this.nodeContext.parent.firstPseudo
            : null;
          this.nodeContext.firstPseudo = new Kr(e, B.num);
        }
        this.nodeContext.inline ||
          this.processAfterIfcontinues(l, u, h, this.context);
        let _ = c["white-space"];
        if (_) {
          let e = ea(_.toString());
          null !== e && (this.nodeContext.whitespace = e);
        }
        let z = c["hyphenate-character"];
        z && z instanceof ue && (this.nodeContext.hyphenateCharacter = z.str);
        let D = c["word-break"],
          H = c["line-break"],
          U = c["overflow-wrap"];
        (D === b.break_all ||
          H === b.anywhere ||
          U === b.break_word ||
          U === b.anywhere) &&
          (this.nodeContext.breakWord = !0),
          this.resolveFormattingContext(this.nodeContext, e, v, r, s, y),
          this.nodeContext.parent &&
            this.nodeContext.parent.formattingContext &&
            (e = this.nodeContext.parent.formattingContext.isFirstTime(
              this.nodeContext,
              e
            )),
          this.nodeContext.inline ||
            ((this.nodeContext.repeatOnBreak = this.processRepeatOnBreak(c)),
            this.findAndProcessRepeatingElements(l, h));
        let $,
          G = !1,
          W = [],
          j = l.namespaceURI,
          X = l.localName,
          Y = X;
        if (
          ("http://www.w3.org/1999/xhtml" == j
            ? ("html" == X ||
              "body" == X ||
              "script" == X ||
              "link" == X ||
              "meta" == X
                ? (X = "div")
                : "vide_" == X
                ? (X = "video")
                : "audi_" == X
                ? (X = "audio")
                : "object" == X && (G = !!this.customRenderer),
              l.getAttribute(va) &&
                null != (n = null == (i = u.content) ? void 0 : i.value) &&
                n.url &&
                (X = "img"))
            : "http://www.idpf.org/2007/ops" == j
            ? ((X = "span"), (j = "http://www.w3.org/1999/xhtml"))
            : "http://www.pyroxy.com/ns/shadow" == j
            ? ((j = "http://www.w3.org/1999/xhtml"),
              (X = this.nodeContext.inline ? "span" : "div"))
            : (G = !!this.customRenderer),
          k)
        )
          e ? (X = "li") : ((X = "div"), (v = b.block), (c.display = v));
        else if ("body" == X || "li" == X) X = "div";
        else if ("q" == X) X = "span";
        else if ("a" == X) {
          let e = c["hyperlink-processing"];
          e && "normal" != e.toString() && (X = "span");
        }
        if (
          (c.behavior &&
            "none" != c.behavior.toString() &&
            this.customRenderer &&
            (G = !0),
          l.dataset &&
            "true" === l.getAttribute("data-math-typeset") &&
            (G = !0),
          G)
        ) {
          let e = this.nodeContext.parent
            ? this.nodeContext.parent.viewNode
            : null;
          $ = this.customRenderer(l, e, c);
        } else $ = T(null);
        $.then((t) => {
          if (t)
            G && (o = "true" == t.getAttribute("data-adapt-process-children"));
          else {
            if (c.display === b.none) return void a.finish(!1);
            t = this.createElement(j, X);
          }
          !y &&
            C &&
            c["column-fill"] === b.auto &&
            (t.setAttribute("data-vivliostyle-column-fill", "auto"),
            (c["column-fill"] = b.balance)),
            X != Y &&
              (t.setAttribute("data-vivliostyle-original-tag", Y),
              ("html" === Y || "body" === Y) && (t.role = "presentation")),
            "a" == X && t.addEventListener("click", this.page.hrefHandler, !1),
            "iframe" == t.localName &&
              "http://www.w3.org/1999/xhtml" == t.namespaceURI &&
              xb(t);
          let i = this.nodeContext.inheritedProps["image-resolution"],
            n = [],
            r = c.width,
            s = c.height,
            h = l.getAttribute("width"),
            u = l.getAttribute("height"),
            d = r === b.auto || (!r && !h),
            p = s === b.auto || (!s && !u),
            f = l.attributes,
            g = f.length,
            m = null,
            v = null;
          for (let i = 0; i < g; i++) {
            let r = f[i],
              s = r.namespaceURI,
              o = r.localName,
              a = r.nodeValue;
            if (s) {
              if ("http://www.w3.org/2000/xmlns/" == s) continue;
              "http://www.w3.org/1999/xlink" == s &&
                "href" == o &&
                (a = this.documentURLTransformer.transformURL(
                  this.resolveURL(a),
                  this.xmldoc.url
                ));
            } else {
              if ((!Xt && o.match(/^on/)) || "style" == o) continue;
              if (("id" == o || "name" == o) && e) {
                let e = this.documentURLTransformer.transformFragment(
                  a,
                  this.xmldoc.url
                );
                t.setAttribute(o, a), t.setAttribute("data-vivliostyle-id", e);
                let i = t.ownerDocument.createElement("a");
                i.setAttribute(o, e),
                  i.setAttribute(Ht, "1"),
                  t.appendChild(i),
                  this.page.registerElementWithId(t, e);
                continue;
              }
              if ("src" == o || "href" == o || "poster" == o)
                (a = this.resolveURL(a)),
                  "href" === o &&
                    (a = this.documentURLTransformer.transformURL(
                      a,
                      this.xmldoc.url
                    ));
              else if ("srcset" == o)
                a = a
                  .split(",")
                  .map((e) => this.resolveURL(e.trim()))
                  .join(",");
              else if (
                "data" === o &&
                G &&
                "object" === X &&
                t.hasAttribute("data")
              )
                continue;
              if (
                "poster" === o &&
                "video" === X &&
                "http://www.w3.org/1999/xhtml" === j &&
                d &&
                p
              ) {
                let e = new Image(),
                  i = On(e, a);
                W.push(i), n.push({ image: e, element: t, fetcher: i });
              }
            }
            if (
              ("http://www.w3.org/2000/svg" == j &&
                /^[A-Z\-]+$/.test(o) &&
                (o = o.toLowerCase()),
              this.isSVGUrlAttribute(o) &&
                (a = Eg(a, this.xmldoc.url, this.documentURLTransformer)),
              s)
            ) {
              let e = bb[s];
              e && (o = `${e}:${o}`);
            }
            if (
              "src" != o ||
              s ||
              ("img" != X && "input" != X) ||
              "http://www.w3.org/1999/xhtml" != j
            )
              if (
                "alt" != o ||
                s ||
                "img" != X ||
                "http://www.w3.org/1999/xhtml" != j
              )
                if (
                  "href" == o &&
                  "image" == X &&
                  "http://www.w3.org/2000/svg" == j &&
                  "http://www.w3.org/1999/xlink" == s
                )
                  this.page.fetchers.push(On(t, a));
                else if (s) t.setAttributeNS(s, o, a);
                else
                  try {
                    t.setAttribute(o, a);
                  } catch (e) {
                    V.warn(e);
                  }
              else v = a;
            else m = a;
          }
          if (m) {
            let e = "input" === X ? new Image() : t,
              o = On(e, m, v);
            e !== t && (t.src = m);
            let a = () => {
              var e, t;
              if (c.position === b.fixed) return !0;
              for (
                let i =
                  null == (e = this.nodeContext.parent) ? void 0 : e.viewNode;
                i && i !== this.viewRoot;
                i = i.parentElement
              )
                if ("fixed" === (null == (t = i.style) ? void 0 : t.position))
                  return !0;
              return !1;
            };
            d ||
            p ||
            (() => {
              let e = this.nodeContext.vertical ? r : s;
              return e instanceof P && "%" === e.unit;
            })() ||
            a()
              ? (d &&
                  p &&
                  i &&
                  1 !== i &&
                  n.push({ image: e, element: t, fetcher: o }),
                W.push(o))
              : this.page.fetchers.push(o);
          }
          delete c.content;
          let x = c["list-style-image"];
          if (x && x instanceof Oe) {
            let e = x.url;
            W.push(On(new Image(), e));
          }
          if (
            (this.preprocessElementStyle(c),
            this.applyComputedStyles(t, c),
            this.nodeContext.inline)
          )
            e || (pt(t, "inline-start"), Es(t) && pt(t, "clone"));
          else if (e) {
            if (!sa(c.position)) {
              let e = c["margin-break"],
                i = this.getBreakTypeAt(this.nodeContext),
                n = null !== i,
                r = "auto" === i;
              ((e === b.discard && n) ||
                (e !== b.keep && r && !this.nodeContext.floatSide)) &&
                Gr(t, "block-start");
            }
          } else {
            let e = this.nodeContext.vertical ? "width" : "height";
            Bt(t, e) &&
              w(t, e, "table-row" === this.nodeContext.display ? "0.01px" : ""),
              pt(t, "block-start"),
              Es(t) && pt(t, "clone"),
              Gr(t, "block-start");
          }
          k && t.setAttribute("value", c["ua-list-item-count"].stringValue()),
            (this.viewNode = t),
            W.length
              ? Bn(W).then(() => {
                  i > 0 &&
                    this.modifyElemDimensionWithImageResolution(
                      n,
                      i,
                      c,
                      this.nodeContext.vertical
                    ),
                    a.finish(o);
                })
              : a.timeSlice().then(() => {
                  a.finish(o);
                });
        });
      }),
      a.result()
    );
  }
  isInsideNonRootMultiColumn() {
    var e;
    let t = null == (e = this.nodeContext.parent) ? void 0 : e.viewNode;
    return !(!t || !qc(t));
  }
  getBreakTypeAt(e) {
    var t, i, n, r, s;
    if (this.isInsideTable(e)) return null;
    for (let n = e; n && !n.after; n = n.parent) {
      if (Ie(n.breakBefore)) return n.breakBefore;
      if (1 === n.fragmentIndex && !n.parent)
        return n.sourceNode === n.sourceNode.ownerDocument.documentElement
          ? "page"
          : null;
      if (null != (t = n.parent) && t.floatSide) return null;
      let e = null == (i = n.parent) ? void 0 : i.viewNode;
      if (e) {
        let t = this.viewport.window.getComputedStyle(e),
          i = parseFloat(t.paddingBlockStart),
          r = parseFloat(t.borderBlockStartWidth);
        if (i || r) return null;
        let s = null == e ? void 0 : e.firstChild;
        for (; s && (de(s, n.parent.whitespace) || (!n.floatSide && Ts(s))); )
          s = s.nextSibling;
        if (s && s !== n.viewNode) return null;
      }
    }
    let o =
      null ==
      (s =
        null ==
        (r = null == (n = this.context) ? void 0 : n.currentLayoutPosition)
          ? void 0
          : r.flowPositions[this.flowName])
        ? void 0
        : s.startBreakType;
    return Ie(o) ? o : "auto";
  }
  isInsideTable(e) {
    for (let t = e; t && !t.after; t = t.parent)
      if (t.display && t.display.startsWith("table")) return !0;
    return !1;
  }
  processAfterIfcontinues(e, t, i, n) {
    let r = this.getPseudoMap(
      t,
      this.regionIds,
      this.isFootnote,
      this.nodeContext,
      n
    );
    if (r && r["after-if-continues"] && r["after-if-continues"].content) {
      let r = new Si(e, t, i, n, this.exprContentListener);
      this.nodeContext.afterIfContinues = new Ta(e, r);
    }
  }
  isSVGUrlAttribute(t) {
    return e.SVG_URL_ATTRIBUTES.includes(t.toLowerCase());
  }
  modifyElemDimensionWithImageResolution(e, t, i, n) {
    e.forEach((e) => {
      if ("load" === e.fetcher.get().get()) {
        let r = e.image,
          s = r.width / t,
          o = r.height / t,
          a = e.element;
        if (s > 0 && o > 0)
          if (
            (i["box-sizing"] === b.border_box &&
              (i["border-left-style"] !== b.none &&
                (s += ke(i["border-left-width"], this.context)),
              i["border-right-style"] !== b.none &&
                (s += ke(i["border-right-width"], this.context)),
              i["border-top-style"] !== b.none &&
                (o += ke(i["border-top-width"], this.context)),
              i["border-bottom-style"] !== b.none &&
                (o += ke(i["border-bottom-width"], this.context))),
            t > 1)
          ) {
            let e = i["max-width"] || b.none,
              t = i["max-height"] || b.none;
            if (e === b.none && t === b.none) w(a, "max-width", `${s}px`);
            else if (e !== b.none && t === b.none) w(a, "width", `${s}px`);
            else if (e === b.none && t !== b.none) w(a, "height", `${o}px`);
            else {
              e.isNumeric(), t.isNumeric();
              let i = e,
                r = t;
              "%" !== i.unit
                ? w(a, "max-width", `${Math.min(s, ke(i, this.context))}px`)
                : "%" !== r.unit
                ? w(a, "max-height", `${Math.min(o, ke(r, this.context))}px`)
                : n
                ? w(a, "height", `${o}px`)
                : w(a, "width", `${s}px`);
            }
          } else if (t < 1) {
            let e = i["min-width"] || ne,
              t = i["min-height"] || ne;
            e.isNumeric(), e.isNumeric();
            let r = e,
              l = t;
            0 === r.num && 0 === l.num
              ? w(a, "min-width", `${s}px`)
              : 0 !== r.num && 0 === l.num
              ? w(a, "width", `${s}px`)
              : 0 === r.num && 0 !== l.num
              ? w(a, "height", `${o}px`)
              : "%" !== r.unit
              ? w(a, "min-width", `${Math.max(s, ke(r, this.context))}px`)
              : "%" !== l.unit
              ? w(a, "min-height", `${Math.max(o, ke(l, this.context))}px`)
              : n
              ? w(a, "height", `${o}px`)
              : w(a, "width", `${s}px`);
          }
      }
    });
  }
  preprocessElementStyle(e) {
    Ge("PREPROCESS_ELEMENT_STYLE").forEach((t) => {
      t(this.nodeContext, e);
    });
  }
  findAndProcessRepeatingElements(e, t) {
    for (let i = e.firstChild; i; i = i.nextSibling) {
      if (1 !== i.nodeType) continue;
      let e = {},
        n = t.getStyle(i, !1);
      if (
        (this.computeStyle(
          this.nodeContext.vertical,
          "rtl" === this.nodeContext.direction,
          n,
          e
        ),
        !this.processRepeatOnBreak(e))
      )
        continue;
      if (
        this.nodeContext.formattingContext instanceof Wt &&
        !this.nodeContext.belongsTo(this.nodeContext.formattingContext)
      )
        return;
      let r = this.nodeContext.parent,
        s = r && r.formattingContext;
      return (
        (this.nodeContext.formattingContext = new Wt(
          s,
          this.nodeContext.sourceNode
        )),
        void this.nodeContext.formattingContext.initializeRepetitiveElements(
          this.nodeContext.vertical
        )
      );
    }
  }
  processRepeatOnBreak(e) {
    let t = e["repeat-on-break"];
    return t !== b.none &&
      ((t === b.auto || M(t)) &&
        (t =
          e.display === b.table_header_group
            ? b.header
            : e.display === b.table_footer_group
            ? b.footer
            : b.none),
      t && t !== b.none)
      ? t.toString()
      : null;
  }
  createTextNodeView() {
    let e = A("createTextNodeView");
    return (
      this.preprocessTextContent().then(() => {
        let t = this.offsetInNode || 0,
          i = Oc(this.nodeContext.preprocessedTextContent).substr(t);
        (this.viewNode = document.createTextNode(i)), e.finish(!0);
      }),
      e.result()
    );
  }
  preprocessTextContent() {
    if (null != this.nodeContext.preprocessedTextContent) return T(!0);
    let e,
      t = (e = this.sourceNode.textContent),
      i = A("preprocessTextContent"),
      n = Ge("PREPROCESS_TEXT_CONTENT"),
      r = 0;
    return (
      i
        .loop(() =>
          r >= n.length
            ? T(!1)
            : n[r++](this.nodeContext, t).thenAsync((e) => ((t = e), T(!0)))
        )
        .then(() => {
          (this.nodeContext.preprocessedTextContent = Uh(e, t)), i.finish(!0);
        }),
      i.result()
    );
  }
  createNodeView(e, t) {
    let i,
      n = A("createNodeView"),
      r = !0;
    return (
      1 == this.sourceNode.nodeType
        ? (i = this.createElementView(e, t))
        : 8 == this.sourceNode.nodeType
        ? ((this.viewNode = null), (i = T(!0)))
        : (i = this.createTextNodeView()),
      i.then((e) => {
        var t, i;
        if (
          ((r = e), (this.nodeContext.viewNode = this.viewNode), this.viewNode)
        ) {
          let e = (e, t) =>
              1 === (null == e ? void 0 : e.nodeType) && Fs(e) === t,
            n = this.nodeContext.parent,
            r = n
              ? e(this.viewNode, "after") &&
                e(n.viewNode, "first-letter") &&
                null != (t = n.viewNode) &&
                t.hasChildNodes()
                ? n.parent.viewNode
                : n.viewNode
              : this.viewRoot;
          if (
            r &&
            (this.nodeContext.inline &&
              !(3 === this.viewNode.nodeType && de(this.viewNode)) &&
              !r.hasChildNodes() &&
              Lc(r).includes("block-start") &&
              pt(r, "text-start"),
            r.appendChild(this.viewNode),
            of(this.viewNode),
            1 === this.viewNode.nodeType &&
              this.viewNode.hasAttribute("data-vivliostyle-id"))
          ) {
            let e = this.viewNode,
              t = e.querySelector(":scope > a:empty[id^='viv-id-']");
            if (
              t &&
              (e.closest("[style*='position: absolute']") ||
                /(table|flex|grid|ruby)/.test(e.style.display))
            ) {
              let e = t.getAttribute("id");
              t.ownerDocument.querySelectorAll(`a[id="${e}"]`).forEach((e) => {
                var t;
                null == (t = e.parentElement) || t.removeChild(e);
              });
              let n = r.closest("[data-vivliostyle-bleed-box]");
              null == (i = null == n ? void 0 : n.parentElement) ||
                i.insertBefore(t, n);
            }
          }
        }
        n.finish(r);
      }),
      n.result()
    );
  }
  setCurrent(e, t, i) {
    return (
      (this.nodeContext = e),
      e
        ? ((this.sourceNode = e.sourceNode),
          (this.offsetInNode = e.offsetInNode))
        : ((this.sourceNode = null), (this.offsetInNode = -1)),
      (this.viewNode = null),
      this.nodeContext ? this.createNodeView(t, !!i) : T(!0)
    );
  }
  processShadowContent(e) {
    if (
      null == e.shadowContext ||
      "content" != e.sourceNode.localName ||
      "http://www.pyroxy.com/ns/shadow" != e.sourceNode.namespaceURI
    )
      return e;
    let t,
      i,
      n = e.boxOffset,
      r = e.shadowContext,
      s = e.parent,
      o = r.subShadow || r.parentShadow;
    r.subShadow
      ? ((t = r.root), (i = r.type), i == Et.ROOTLESS && (t = t.firstChild))
      : ((t = r.owner.firstChild), (i = Et.ROOTLESS));
    let a = e.sourceNode.nextSibling;
    if (
      (a
        ? ((e.sourceNode = a), e.resetView())
        : e.shadowSibling
        ? (e = e.shadowSibling)
        : t
        ? (e = null)
        : ((e = e.parent.modify()).after = !0),
      t)
    ) {
      let r = new zn(t, s, n);
      return (
        (r.shadowContext = o), (r.shadowType = i), (r.shadowSibling = e), r
      );
    }
    return (e.boxOffset = n), e;
  }
  nextPositionInTree(e) {
    let t = e.boxOffset + 1;
    if (e.after) {
      if (!e.parent) return null;
      if (e.shadowType != Et.ROOTED) {
        let i = e.sourceNode.nextSibling;
        if (i)
          return (
            ((e = e.modify()).boxOffset = t),
            (e.sourceNode = i),
            e.resetView(),
            this.processShadowContent(e)
          );
      }
      return e.shadowSibling
        ? (((e = e.shadowSibling.modify()).boxOffset = t), e)
        : (((e = e.parent.modify()).boxOffset = t), (e.after = !0), e);
    }
    {
      if (e.nodeShadow) {
        let i = e.nodeShadow.root;
        if ((e.nodeShadow.type == Et.ROOTLESS && (i = i.firstChild), i)) {
          let n = new zn(i, e, t);
          return (
            (n.shadowContext = e.nodeShadow),
            (n.shadowType = e.nodeShadow.type),
            this.processShadowContent(n)
          );
        }
      }
      let i = e.sourceNode.firstChild;
      if (i) return this.processShadowContent(new zn(i, e, t));
      if (1 != e.sourceNode.nodeType) {
        t += Oc(e.preprocessedTextContent).length - 1 - e.offsetInNode;
      }
      return ((e = e.modify()).boxOffset = t), (e.after = !0), e;
    }
  }
  isTransclusion(e, t, i) {
    let n = he(t, "hyperlink-processing");
    if (!n) return !1;
    let r = n.evaluate(this.context, "hyperlink-processing");
    return !!r && r.toString() == i;
  }
  nextInTree(e, t) {
    let i = this.nextPositionInTree(e);
    if (!i || i.after) return T(i);
    let n = A("nextInTree");
    return (
      this.setCurrent(i, !0, t).then((e) => {
        (!i.viewNode || !e) &&
          ((i = i.modify()), (i.after = !0), i.viewNode || (i.inline = !0)),
          this.dispatchEvent({ type: "nextInTree", nodeContext: i }),
          n.finish(i);
      }),
      n.result()
    );
  }
  addImageFetchers(e) {
    if (e instanceof ge) {
      let t = e.values;
      for (let e = 0; e < t.length; e++) this.addImageFetchers(t[e]);
    } else if (e instanceof Oe) {
      let t = e.url;
      this.page.fetchers.push(On(new Image(), t));
    }
  }
  applyComputedStyles(e, t) {
    var i, n, r;
    let s = t["background-image"];
    s && this.addImageFetchers(s);
    let o,
      a,
      l = t.position === b.relative,
      h =
        null === (null == (i = this.nodeContext) ? void 0 : i.parent) &&
        null === (null == (n = this.sourceNode) ? void 0 : n.parentElement) &&
        !(null == (r = this.viewRoot) || !r.parentElement),
      u = Object.keys(t);
    u.sort(Wl);
    for (let i of u) {
      if (yb[i]) continue;
      let n = t[i];
      if (n && (n !== O || bt(i))) {
        if (
          ((n = n.visit(new Ir(this.xmldoc.url, this.documentURLTransformer))),
          n instanceof P &&
            Tr(n.unit) &&
            (n =
              "lh" === n.unit
                ? new P(n.num * this.getLineHeightUnitSize(i, o, a), "px")
                : kr(n, this.context)),
          "font-size" === i ? (o = n) : "line-height" === i && (a = n),
          Wh[i] || (l && $h[i]))
        ) {
          this.page.delayedItems.push(new Hn(e, i, n));
          continue;
        }
        h && this.page.pageAreaElement && Zn(i)
          ? w(
              this.page.pageAreaElement.parentElement.parentElement,
              i,
              n.toString()
            )
          : w(e, i, n.toString());
      }
    }
  }
  getLineHeightUnitSize(e, t, i) {
    var n, r, s, o;
    let a = Y.lh / Y.em,
      l =
        1 ===
        (null ==
        (r = null == (n = this.nodeContext.parent) ? void 0 : n.viewNode)
          ? void 0
          : r.nodeType)
          ? this.viewport.window.getComputedStyle(
              this.nodeContext.parent.viewNode
            )
          : null,
      h = l ? parseFloat(l.fontSize) : this.context.fontSize(),
      u = l
        ? "normal" === l.lineHeight
          ? h * a
          : parseFloat(l.lineHeight)
        : this.context.rootLineHeight;
    if ("line-height" === e || "font-size" === e) return u;
    let c = null;
    if (i) {
      if (
        i instanceof nt ||
        (i instanceof P && ("em" === i.unit || "%" === i.unit))
      )
        c = i instanceof P && "%" === i.unit ? i.num / 100 : i.num;
      else if (i instanceof P)
        return i.num * this.context.queryUnitSize(i.unit, !1);
    } else
      for (
        let e = null == (s = this.nodeContext.parent) ? void 0 : s.viewNode;
        ;
        e = e.parentNode
      ) {
        if (!e || 1 !== e.nodeType) {
          c = a;
          break;
        }
        let t =
          null == (o = null == e ? void 0 : e.style) ? void 0 : o.lineHeight;
        if (t) {
          /^[0-9.]+$/.test(t) && (c = parseFloat(t)), "normal" === t && (c = a);
          break;
        }
      }
    return null !== c
      ? t instanceof P
        ? c * ju(t, h, this.context).num
        : c * h
      : u;
  }
  applyPseudoelementStyle(e, t, i) {
    if (e.after) return;
    let n = this.sourceNode,
      r = (e.shadowContext ? e.shadowContext.styler : this.styler).getStyle(
        n,
        !1
      ),
      s = Tt(r, "_pseudos");
    if (!s || ((r = s[t]), !r)) return;
    let o = {};
    e.vertical = this.computeStyle(e.vertical, "rtl" === e.direction, r, o);
    let a = o.content;
    St(a) &&
      (a.visit(new mn(i, this.context, a, this.exprContentListener)),
      delete o.content),
      this.applyComputedStyles(i, o);
  }
  peelOff(e, t) {
    let i = A("peelOff"),
      n = e.firstPseudo,
      r = e.offsetInNode,
      s = e.after;
    if (t > 0) {
      let i = e.viewNode.textContent;
      (e.viewNode.textContent = i.substr(0, t)), (r += t);
    } else if (!s && e.viewNode && 0 == r) {
      let t = e.viewNode.parentNode;
      t && t.removeChild(e.viewNode);
    }
    let o = e.boxOffset + t,
      a = [];
    for (; e.firstPseudo === n; ) a.push(e), (e = e.parent);
    let l = a.pop(),
      h = l.shadowSibling;
    return (
      i
        .loop(() => {
          for (; a.length > 0; ) {
            (l = a.pop()),
              (e = new zn(l.sourceNode, e, o)),
              0 == a.length && ((e.offsetInNode = r), (e.after = s)),
              (e.shadowType = l.shadowType),
              (e.shadowContext = l.shadowContext),
              (e.nodeShadow = l.nodeShadow),
              (e.shadowSibling = l.shadowSibling ? l.shadowSibling : h),
              (h = null);
            let t = this.setCurrent(e, !1);
            if (t.isPending()) return t;
          }
          return T(!1);
        })
        .then(() => {
          i.finish(e);
        }),
      i.result()
    );
  }
  createElement(e, t) {
    return "http://www.w3.org/1999/xhtml" == e
      ? this.document.createElement(t)
      : this.document.createElementNS(e, t);
  }
  applyFootnoteStyle(e, t, i) {
    let n = {},
      r = Tt(this.footnoteStyle, "_pseudos");
    if (((e = this.computeStyle(e, t, this.footnoteStyle, n)), r && r.before)) {
      let n = {},
        s = this.createElement("http://www.w3.org/1999/xhtml", "span");
      Ni(s, "before"),
        i.appendChild(s),
        this.computeStyle(e, t, r.before, n),
        delete n.content,
        this.applyComputedStyles(s, n);
    }
    return delete n.content, this.applyComputedStyles(i, n), e;
  }
  processFragmentedBlockEdge(e) {
    var t, i, n;
    let r = !e.inline && e.after ? e.parent : e,
      s = !1;
    if (
      e.inline &&
      e.after &&
      !e.shadowContext &&
      1 === (null == (t = e.sourceNode.nextSibling) ? void 0 : t.nodeType)
    ) {
      let t = e.sourceNode.nextSibling,
        n =
          null == (i = he(this.styler.getStyle(t, !1), "display"))
            ? void 0
            : i.value.toString();
      s =
        (n && !Gt(n)) ||
        ("true" === t.getAttribute("data-math-typeset") &&
          /^\s*(\$\$|\\\[)/.test(t.textContent));
    }
    let o = 0;
    for (let e = r; e; e = e.parent) {
      if (1 !== (null == (n = e.viewNode) ? void 0 : n.nodeType)) continue;
      let t = e.viewNode;
      if (t.style)
        if (e.inline) {
          if ((pt(t, "inline-end"), Es(t))) {
            let i = e.vertical ? t.offsetWidth : t.offsetHeight;
            pt(t, "clone"),
              (e.vertical ? t.offsetWidth : t.offsetHeight) > i &&
                this.fixClonedBoxDecorationOverflow(t);
          }
        } else {
          let i = this.nodeContext.vertical ? "width" : "height";
          if ((Bt(t, i) && w(t, i, ""), pt(t, "block-end"), !o++ && e !== r)) {
            let { textAlign: e } = this.viewport.window.getComputedStyle(t);
            if ("justify" !== e || s) pt(t, "text-end");
            else {
              let e = this.createChildAnonymousBlockIfNeeded(t);
              e
                ? e !== t
                  ? Rc(e, [
                      "block-start",
                      "text-start",
                      "block-end",
                      "text-end",
                      "justify",
                    ])
                  : (pt(t, "text-end"), pt(t, "justify"))
                : pt(t, "text-end");
            }
          }
          Es(t) && pt(t, "clone"), Gr(t, "block-end");
        }
    }
  }
  fixClonedBoxDecorationOverflow(e) {
    let t = this.viewport.window.getComputedStyle(e),
      i = -(
        parseFloat(t.paddingInlineEnd) + parseFloat(t.borderInlineEndWidth)
      );
    isNaN(i) || (e.style.marginInlineEnd = `${i}px`);
  }
  createChildAnonymousBlockIfNeeded(e) {
    let t = (e) => {
        let {
          display: i,
          position: n,
          float: r,
        } = this.viewport.window.getComputedStyle(e);
        if ("ruby" === e.localName || Zt[e.localName]) return !1;
        if ("br" === e.localName) return !0;
        if (("inline" === i || "contents" === i) && e.hasChildNodes()) {
          let i = e.lastElementChild;
          if (
            i &&
            (!i.nextSibling ||
              (i.nextSibling === e.lastChild && de(i.nextSibling)))
          ) {
            let e = t(i);
            if (e || null === e) return e;
          }
          for (
            let e = null == i ? void 0 : i.previousElementSibling;
            e;
            e = e.previousElementSibling
          ) {
            let i = t(e);
            if (i || null === i) return null;
          }
          return !1;
        }
        if (
          "none" === i ||
          "absolute" === n ||
          "fixed" === n ||
          (r && "none" !== r) ||
          e.hasAttribute(Ht)
        ) {
          let i = e.previousElementSibling;
          return (
            !(
              !i ||
              !(
                i.nextSibling === e ||
                (i.nextSibling === e.previousSibling && de(i.nextSibling))
              )
            ) && t(i)
          );
        }
        return !(!i || Gt(i));
      },
      i = null;
    for (let n = e.lastElementChild; n; n = n.previousElementSibling) {
      let e = t(n);
      if (e) {
        i = n;
        break;
      }
      if (null === e) return null;
    }
    if (!i) return e;
    if (
      i === e.lastElementChild &&
      (!i.nextSibling || (i.nextSibling === e.lastChild && de(i.nextSibling)))
    )
      return null;
    let n = e.ownerDocument.createElement("span");
    n.className = "viv-anonymous-block";
    for (let e = i.nextSibling, t = null; e; e = t)
      (t = e.nextSibling), n.appendChild(e);
    return e.appendChild(n), n;
  }
  convertLengthToPx(e, t, i) {
    let n = e.num,
      r = e.unit;
    if (Qp(r)) {
      let n = t;
      for (; n && 1 !== n.nodeType; ) n = n.parentNode;
      let r = parseFloat(i.getElementComputedStyle(n)["font-size"]);
      return this.context, ba(e, r, this.context).num;
    }
    {
      let t = this.context.queryUnitSize(r, !1);
      return t ? n * t : e;
    }
  }
  isSameNodePositionStep(e, t) {
    if (e.shadowContext) {
      if (!t.shadowContext) return !1;
      let i = 1 === e.node.nodeType ? e.node : e.node.parentElement,
        n = 1 === t.node.nodeType ? t.node : t.node.parentElement;
      return e.shadowContext.owner === t.shadowContext.owner && Fs(i) === Fs(n);
    }
    return e.node === t.node;
  }
  isSameNodePosition(e, t) {
    return (
      e.offsetInNode === t.offsetInNode &&
      e.after == t.after &&
      e.steps.length === t.steps.length &&
      e.steps.every((e, i) => {
        let n = t.steps[i];
        return this.isSameNodePositionStep(e, n);
      })
    );
  }
  isPseudoelement(e) {
    return !!Fs(e);
  }
};
p(Xi, "SVG_URL_ATTRIBUTES", [
  "color-profile",
  "clip-path",
  "cursor",
  "filter",
  "marker",
  "marker-start",
  "marker-end",
  "marker-mid",
  "fill",
  "stroke",
  "mask",
]);
var Qa = Xi,
  yb = {
    "float-min-wrap-block": !0,
    "float-reference": !0,
    "flow-into": !0,
    "flow-linger": !0,
    "flow-options": !0,
    "flow-priority": !0,
    "footnote-policy": !0,
    "margin-break": !0,
    page: !0,
  },
  Za = null,
  Ja = class {
    constructor(e) {
      p(this, "layoutBox"),
        p(this, "window"),
        p(this, "scaleRatio"),
        (this.layoutBox = e.layoutBox),
        (this.window = e.window),
        (this.scaleRatio = e.scaleRatio);
    }
    scaleRect(e) {
      return Za
        ? {
            left: e.left * this.scaleRatio,
            top: e.top * this.scaleRatio,
            right: e.right * this.scaleRatio,
            bottom: e.bottom * this.scaleRatio,
            width: e.width * this.scaleRatio,
            height: e.height * this.scaleRatio,
          }
        : e;
    }
    subtractOffsets(e, t) {
      let i = t.left,
        n = t.top;
      return {
        left: e.left - i,
        top: e.top - n,
        right: e.right - i,
        bottom: e.bottom - n,
        width: e.width,
        height: e.height,
      };
    }
    getRangeClientRects(e) {
      let t = e.getClientRects(),
        i = this.layoutBox.getBoundingClientRect();
      return Array.from(t).map((e) =>
        this.scaleRect(this.subtractOffsets(e, i))
      );
    }
    getElementClientRect(e) {
      let t = e.getBoundingClientRect();
      if (0 === t.left && 0 === t.top && 0 === t.right && 0 === t.bottom)
        return t;
      let i = this.layoutBox.getBoundingClientRect();
      return this.scaleRect(this.subtractOffsets(t, i));
    }
    getElementComputedStyle(e) {
      return this.window.getComputedStyle(e, null);
    }
    adjustLengthValue(e) {
      let t = 64 * (this.scaleRatio || 1);
      return Math.floor(e * t) / t;
    }
  },
  wn = class {
    constructor(e, t, i, n, r, s) {
      (this.window = e),
        (this.fontSize = t),
        (this.pixelRatio = i),
        p(this, "document"),
        p(this, "root"),
        p(this, "outerZoomBox"),
        p(this, "contentContainer"),
        p(this, "layoutBox"),
        p(this, "width"),
        p(this, "height"),
        p(this, "scaleRatio", 1),
        (this.document = e.document),
        (this.root = n || this.document.body);
      let o = this.root.firstElementChild;
      o ||
        ((o = this.document.createElement("div")),
        o.setAttribute("data-vivliostyle-outer-zoom-box", "true"),
        (o.role = "presentation"),
        this.root.appendChild(o));
      let a = o.firstElementChild;
      a ||
        ((a = this.document.createElement("div")),
        a.setAttribute("data-vivliostyle-spread-container", "true"),
        (a.role = "presentation"),
        o.appendChild(a)),
        i > 0 &&
          CSS.supports("zoom", "8") &&
          (w(this.root, "--viv-outputPixelRatio", `${i}`),
          w(this.root, "--viv-devicePixelRatio", `${e.devicePixelRatio}`),
          (this.scaleRatio = i / e.devicePixelRatio),
          null === Za && (Za = !("currentCSSZoom" in a)),
          Za && w(this.root, "--viv-scaleRectRatio", `${this.scaleRatio}`));
      let l = o.nextElementSibling;
      l ||
        ((l = this.document.createElement("div")),
        l.setAttribute("data-vivliostyle-layout-box", "true"),
        (l.role = "presentation"),
        this.root.appendChild(l)),
        (this.outerZoomBox = o),
        (this.contentContainer = a),
        (this.layoutBox = l),
        (this.width =
          r ||
          parseFloat(e.getComputedStyle(this.root).width) ||
          this.root.offsetWidth ||
          e.innerWidth),
        (this.height =
          s ||
          parseFloat(e.getComputedStyle(this.root).height) ||
          this.root.offsetHeight ||
          e.innerHeight);
      let h = 794,
        u = 1056,
        c =
          (!e.outerWidth && !e.outerHeight) ||
          /Headless/.test(navigator.userAgent) ||
          (navigator.webdriver &&
            800 === e.innerWidth &&
            600 === e.innerHeight);
      (!this.width || (!r && c)) && (this.width = h),
        (!this.height || (!s && c)) && (this.height = u);
    }
    resetZoom() {
      w(this.outerZoomBox, "width", ""),
        w(this.outerZoomBox, "height", ""),
        w(this.contentContainer, "width", ""),
        w(this.contentContainer, "height", ""),
        w(this.contentContainer, "transform", "");
    }
    zoom(e, t, i) {
      w(this.root, "--viv-outputScale", `${i}`),
        w(this.outerZoomBox, "width", e * i + "px"),
        w(this.outerZoomBox, "height", t * i + "px"),
        w(this.contentContainer, "width", `${e}px`),
        w(this.contentContainer, "height", `${t}px`);
    }
    clear() {
      let e = this.root;
      for (; e.lastChild; ) e.removeChild(e.lastChild);
    }
  },
  ji = class {
    constructor(e, t, i) {
      (this.store = e),
        (this.url = t),
        (this.document = i),
        p(this, "lang", null),
        p(this, "totalOffset", -1),
        p(this, "root"),
        p(this, "body"),
        p(this, "head"),
        p(this, "last"),
        p(this, "lastOffset", 1),
        p(this, "idMap"),
        (this.root = i.documentElement);
      let n = null,
        r = null;
      if ("http://www.w3.org/1999/xhtml" == this.root.namespaceURI) {
        for (let e = this.root.firstChild; e; e = e.nextSibling) {
          if (1 != e.nodeType) continue;
          let t = e;
          if ("http://www.w3.org/1999/xhtml" == t.namespaceURI)
            switch (t.localName) {
              case "head":
                r = t;
                break;
              case "body":
                n = t;
            }
        }
        this.lang = this.root.getAttribute("lang");
      }
      (this.body = n),
        (this.head = r),
        (this.last = this.root),
        this.last.setAttribute(Ft, "0");
    }
    doc() {
      return new nl([this.document]);
    }
    getElementOffset(e) {
      let t = e.getAttribute(Ft);
      if (t) return parseInt(t, 10);
      let i = this.lastOffset,
        n = this.last;
      for (; n != e; ) {
        let e = n.firstChild;
        if (!e)
          for (; (e = n.nextSibling), !e; )
            if (((n = n.parentNode), null == n))
              throw new Error("Internal error");
        (n = e),
          1 == e.nodeType
            ? (e.setAttribute(Ft, i.toString()), ++i)
            : (i += e.textContent.length);
      }
      return (this.lastOffset = i), (this.last = e), i - 1;
    }
    getNodeOffset(e, t, i) {
      let n = 0,
        r = e,
        s = null;
      if (1 == r.nodeType) {
        if (!i) return this.getElementOffset(r);
      } else {
        if (((n = t), (s = r.previousSibling), !s))
          return (r = r.parentNode), (n += 1), this.getElementOffset(r) + n;
        r = s;
      }
      for (;;) {
        for (; r.lastChild; ) r = r.lastChild;
        if (1 == r.nodeType) break;
        if (((n += r.textContent.length), (s = r.previousSibling), !s)) {
          r = r.parentNode;
          break;
        }
        r = s;
      }
      return (n += 1), this.getElementOffset(r) + n;
    }
    getTotalOffset() {
      return (
        this.totalOffset < 0 &&
          (this.totalOffset = this.getNodeOffset(this.root, 0, !0)),
        this.totalOffset
      );
    }
    getNodeByOffset(e) {
      let t,
        i = this.root;
      for (;;) {
        if (((t = this.getElementOffset(i)), t >= e)) return i;
        let n = i.children;
        if (!n) break;
        let r = gt(n.length, (t) => {
          let i = n[t];
          return this.getElementOffset(i) > e;
        });
        if (0 == r) break;
        i = n[r - 1];
      }
      let n = t + 1,
        r = i,
        s = r.firstChild || r.nextSibling,
        o = null;
      for (;;) {
        if (s) {
          if (
            1 == s.nodeType ||
            ((r = s),
            (o = r),
            (n += s.textContent.length),
            n > e && !/^\s*$/.test(s.textContent))
          )
            break;
        } else if (((r = r.parentNode), !r)) break;
        s = r.nextSibling;
      }
      return s && o && /^\s*$/.test(o.textContent) && (o = s), o || i;
    }
    buildIdMap(e) {
      let t = e.getAttribute("id");
      t && !this.idMap[t] && (this.idMap[t] = e);
      let i = e.getAttributeNS("http://www.w3.org/XML/1998/namespace", "id");
      i && !this.idMap[i] && (this.idMap[i] = e);
      for (let t = e.firstElementChild; t; t = t.nextElementSibling)
        this.buildIdMap(t);
    }
    getElement(e) {
      let t = e.match(/([^#]*)#(.+)$/);
      if (!t || (t[1] && t[1] != this.url)) return null;
      let i = t[2],
        n = this.document.getElementById(i);
      return (
        !n &&
          this.document.getElementsByName &&
          (n = this.document.getElementsByName(i)[0]),
        n ||
          (this.idMap ||
            ((this.idMap = {}), this.buildIdMap(this.document.documentElement)),
          (n = this.idMap[i])),
        n
      );
    }
  },
  Ep = ((e) => (
    (e.TEXT_HTML = "text/html"),
    (e.TEXT_XML = "text/xml"),
    (e.APPLICATION_XML = "application/xml"),
    (e.APPLICATION_XHTML_XML = "application/xhtml+xml"),
    (e.IMAGE_SVG_XML = "image/svg+xml"),
    e
  ))(Ep || {});
function tl(e, t, i) {
  let n,
    r = i || new DOMParser();
  try {
    n = r.parseFromString(e, t);
  } catch (e) {}
  if (!n) return null;
  {
    let e = n.documentElement,
      t = "parsererror";
    if (e.localName === t) return null;
    for (let i = e.firstElementChild; i; i = i.nextElementSibling)
      if (i.localName === t) return null;
  }
  return n;
}
function Eb(e) {
  let t = e.contentType;
  if (t) {
    let e = Object.keys(Ep);
    for (let i = 0; i < e.length; i++) if (Ep[e[i]] === t) return t;
    if (t.match(/\+xml$/)) return "application/xml";
  }
  let i = e.url.match(/\.([^./]+)$/);
  if (i)
    switch (i[1]) {
      case "html":
      case "htm":
        return "text/html";
      case "xhtml":
      case "xht":
        return "application/xhtml+xml";
      case "svg":
      case "svgz":
        return "image/svg+xml";
      case "opf":
      case "xml":
        return "application/xml";
    }
  return null;
}
function Np(e, t) {
  let i = e.responseXML;
  if (!i) {
    let t = new DOMParser(),
      n = e.responseText;
    if (n) {
      let r = Eb(e);
      if (((i = tl(n, r || "application/xml", t)), i && !r)) {
        let e = i.documentElement;
        "html" !== e.localName.toLowerCase() || e.namespaceURI
          ? "svg" === e.localName.toLowerCase() &&
            "image/svg+xml" !== i.contentType &&
            (i = tl(n, "image/svg+xml", t))
          : (i = tl(n, "text/html", t));
      }
      i || (i = tl(n, "text/html", t));
    }
  }
  return T(i ? new ji(t, e.url, i) : null);
}
function Sg() {
  return new Cs(Np, "document");
}
var Sp = class e {
    constructor(e) {
      this.fn = e;
    }
    check(e) {
      return this.fn(e);
    }
    withAttribute(t, i) {
      return new e(
        (e) => this.check(e) && 1 == e.nodeType && e.getAttribute(t) == i
      );
    }
    withChild(t, i) {
      return new e((e) => {
        if (!this.check(e)) return !1;
        let n = new nl([e]);
        return (n = n.child(t)), i && (n = n.predicate(i)), n.size() > 0;
      });
    }
  },
  vp = new Sp((e) => !0),
  nl = class e {
    constructor(e) {
      this.nodes = e;
    }
    asArray() {
      return this.nodes;
    }
    size() {
      return this.nodes.length;
    }
    predicate(t) {
      let i = [];
      for (let e of this.nodes) t.check(e) && i.push(e);
      return new e(i);
    }
    forEachNode(t) {
      let i = [],
        n = (e) => {
          i.push(e);
        };
      for (let e = 0; e < this.nodes.length; e++) t(this.nodes[e], n);
      return new e(i);
    }
    forEach(e) {
      let t = [];
      for (let i = 0; i < this.nodes.length; i++) t.push(e(this.nodes[i]));
      return t;
    }
    forEachNonNull(e) {
      let t = [];
      for (let i = 0; i < this.nodes.length; i++) {
        let n = e(this.nodes[i]);
        null != n && t.push(n);
      }
      return t;
    }
    child(e) {
      return this.forEachNode((t, i) => {
        for (let n = t.firstChild; n; n = n.nextSibling)
          1 == n.nodeType && n.localName == e && i(n);
      });
    }
    childElements() {
      return this.forEachNode((e, t) => {
        for (let i = e.firstChild; i; i = i.nextSibling)
          1 == i.nodeType && t(i);
      });
    }
    attribute(e) {
      return this.forEachNonNull((t) =>
        1 == t.nodeType ? t.getAttribute(e) : null
      );
    }
    textContent() {
      return this.forEach((e) => e.textContent);
    }
  },
  Sb = new tn(() => {
    let e = A("uaStylesheetBase"),
      t = Yd(),
      i = J("user-agent-base.css", qt),
      n = new Nn(null, null, null, null, null, t, !0);
    return (
      n.startStylesheet("UA"),
      Ef(n.cascade),
      zr(Ec, n, i, null, null).thenFinish(e),
      e.result()
    );
  }, "uaStylesheetBaseFetcher");
function Nb() {
  return Sb.get();
}
var Tp = class {
    constructor(e, t, i, n, r, s, o, a, l, h) {
      (this.store = e),
        (this.rootScope = t),
        (this.pageScope = i),
        (this.cascade = n),
        (this.rootBox = r),
        (this.fontFaces = s),
        (this.footnoteProps = o),
        (this.flowProps = a),
        (this.viewportProps = l),
        (this.pageProps = h),
        p(this, "fontDeobfuscator"),
        p(this, "validatorSet"),
        (this.fontDeobfuscator = e.fontDeobfuscator),
        (this.validatorSet = e.validatorSet),
        this.pageScope.defineBuiltIn("has-content", function (e) {
          let t = this,
            i = t.currentLayoutPosition,
            n = i.firstFlowChunkOfFlow(e);
          return (
            t.matchPageSide(i.startSideOfFlow(e)) &&
            i.hasContent(e, t.lookupOffset) &&
            !!n &&
            !t.flowChunkIsAfterParentFlowForcedBreak(n)
          );
        }),
        this.pageScope.defineName(
          "page-number",
          new Z(
            this.pageScope,
            function () {
              return this.pageNumberOffset + this.currentLayoutPosition.page;
            },
            "page-number"
          )
        ),
        this.pageScope.defineName(
          "blank-page",
          new Z(
            this.pageScope,
            function () {
              let e = this.currentLayoutPosition;
              return null == e ? void 0 : e.isBlankPage;
            },
            "blank-page"
          )
        );
    }
    sizeViewport(e, t, i, n) {
      if (this.viewportProps.length) {
        let r = new js(this.rootScope, e, t, i),
          s = xf(r, this.viewportProps),
          o = s.width,
          a = s.height,
          l = s["text-zoom"],
          h = 1;
        if ((o && a) || l) {
          let s = Y.em;
          if (
            ((l ? l.evaluate(r, "text-zoom") : null) === b.scale &&
              ((h = s / i), (i = s), (e *= h), (t *= h)),
            o && a)
          ) {
            let e = ke(o.evaluate(r, "width"), r),
              t = ke(a.evaluate(r, "height"), r);
            if (e > 0 && t > 0)
              return {
                width: n && n.spreadView ? 2 * (e + n.pageBorder) : e,
                height: t,
                fontSize: i,
              };
          }
        }
      }
      return { width: e, height: t, fontSize: i };
    }
  },
  ss = class extends js {
    constructor(e, t, i, n, r, s, o, a, l, h, u, c, d) {
      super(e.rootScope, n.width, n.height, n.fontSize),
        (this.style = e),
        (this.xmldoc = t),
        (this.viewport = n),
        (this.clientLayout = r),
        (this.fontMapper = s),
        (this.customRenderer = o),
        (this.fallbackMap = a),
        (this.pageNumberOffset = l),
        (this.documentURLTransformer = h),
        (this.counterStore = u),
        p(this, "lang"),
        p(this, "primaryFlows", { body: !0 }),
        p(this, "rootPageBoxInstance", null),
        p(this, "styler", null),
        p(this, "stylerMap", null),
        p(this, "currentLayoutPosition", null),
        p(this, "layoutPositionAtPageStart", null),
        p(this, "lookupOffset", 0),
        p(this, "faces"),
        p(this, "pageBoxInstances", {}),
        p(this, "pageManager", null),
        p(this, "rootPageFloatLayoutContext"),
        p(this, "pageBreaks", {}),
        p(this, "pageProgression", null),
        p(this, "isVersoFirstPage", !1),
        p(this, "blankPageAtStart", !1),
        p(this, "pageSheetSize", {}),
        p(this, "pageSheetHeight", 0),
        p(this, "pageSheetWidth", 0),
        (this.lang = t.lang || i),
        (this.faces = new Ma(this.style.fontDeobfuscator)),
        (this.rootPageFloatLayoutContext = new Cn(
          null,
          null,
          null,
          null,
          null,
          null,
          null
        )),
        (this.pageProgression = c || null),
        (this.isVersoFirstPage = !!d);
      for (let t in e.flowProps) {
        let i = he(e.flowProps[t], "flow-consume");
        i &&
          (i.evaluate(this, "flow-consume") == b.all
            ? (this.primaryFlows[t] = !0)
            : delete this.primaryFlows[t]);
      }
    }
    init() {
      let e = A("StyleInstance.init"),
        t = this.counterStore.createCounterListener(this.xmldoc.url),
        i = this.counterStore.createCounterResolver(
          this.xmldoc.url,
          this.style.rootScope,
          this.style.pageScope
        );
      (this.styler = new Li(
        this.xmldoc,
        this.style.cascade,
        this.style.rootScope,
        this,
        this.primaryFlows,
        this.style.validatorSet,
        t,
        i
      )),
        i.setStyler(this.styler),
        this.styler.resetFlowChunkStream(this),
        (this.stylerMap = {}),
        (this.stylerMap[this.xmldoc.url] = this.styler);
      let n = this.styler.getTopContainerStyle();
      this.pageProgression || (this.pageProgression = mp(n)),
        this.matchStartPageSide(this.styler.breakBeforeValues[0]) ||
          (0 === this.pageNumberOffset
            ? (this.isVersoFirstPage = !0)
            : (this.blankPageAtStart = !0));
      let r = this.style.rootBox;
      this.rootPageBoxInstance = new Ga(r);
      let s = this.style.cascade.createInstance(this, t, i, this.lang);
      (this.styler.cascade.currentPageType = this.styler.cascade.firstPageType),
        this.rootPageBoxInstance.applyCascadeAndInit(s, n),
        this.rootPageBoxInstance.resolveAutoSizing(this),
        (this.pageManager = new ja(
          s,
          this.style.pageScope,
          this.rootPageBoxInstance,
          this,
          n
        ));
      let o = [];
      for (let e of this.style.fontFaces) {
        if (e.condition && !e.condition.evaluate(this)) continue;
        let t = ig(e.properties, this),
          i = new Oi(t);
        o.push(i);
      }
      this.fontMapper.findOrLoadFonts(o, this.faces).then(() => {
        xg(this.xmldoc.document, this.viewport.window, this.styler).thenFinish(
          e
        );
      });
      let a = this.style.pageProps;
      return (
        a[""] || (a[""] = {}),
        Object.keys(a).forEach((e) => {
          let t = a[e];
          this.styler.cascade.applyVarFilter([t], this.styler, null),
            this.styler.cascade.applyCalcFilter(t, this.styler.context);
          let i = Cp($i(t), this);
          this.pageSheetSize[e] = {
            width: i.pageWidth + 2 * i.cropOffset,
            height: i.pageHeight + 2 * i.cropOffset,
          };
        }),
        e.result()
      );
    }
    matchStartPageSide(e) {
      let t = this.pageNumberOffset % 2 == (this.isVersoFirstPage ? 1 : 0),
        i = "ltr" == this.pageProgression;
      switch (e) {
        case "left":
          return t !== i;
        case "right":
          return t === i;
        case "recto":
          return t;
        case "verso":
          return !t;
        default:
          return !0;
      }
    }
    getStylerForDoc(e) {
      let t = this.stylerMap[e.url];
      if (!t) {
        let i = this.style.store.getStyleForDoc(e),
          n = new js(
            i.rootScope,
            this.pageWidth(),
            this.pageHeight(),
            this.initialFontSize
          ),
          r = this.counterStore.createCounterListener(e.url),
          s = this.counterStore.createCounterResolver(
            e.url,
            i.rootScope,
            i.pageScope
          );
        (t = new Li(
          e,
          i.cascade,
          i.rootScope,
          n,
          this.primaryFlows,
          i.validatorSet,
          r,
          s
        )),
          (this.stylerMap[e.url] = t);
      }
      return t;
    }
    registerInstance(e, t) {
      this.pageBoxInstances[e] = t;
    }
    lookupInstance(e) {
      return this.pageBoxInstances[e];
    }
    encounteredFlowChunk(e, t) {
      let i = this.currentLayoutPosition;
      if (i) {
        i.flows[e.flowName]
          ? (t = i.flows[e.flowName])
          : (i.flows[e.flowName] = t);
        let n = i.flowPositions[e.flowName];
        n || ((n = new Qr()), (i.flowPositions[e.flowName] = n));
        let r = qh(e.element),
          s = new ht(r),
          o = new Zr(s, e);
        n.positions.push(o);
      }
    }
    evalSupportsTest(e, t, i) {
      if (i) return "selector" === e && this.evalSupportsSelector(t);
      if (!e) return !1;
      let n = !0;
      let r = new (class {
          unknownProperty(e, t) {
            n = !1;
          }
          invalidPropertyValue(e, t) {
            n = !1;
          }
          simpleProperty(e, t, i) {}
        })(),
        s = sn(this.style.rootScope, new De(t, null), "");
      return (
        !!s &&
        (this.xmldoc.store.validatorSet.validatePropertyAndHandleShorthand(
          e,
          s,
          !1,
          r
        ),
        n)
      );
    }
    evalSupportsSelector(e) {
      let t = new sl(null),
        i = new De(e + "{}", t);
      return (
        !!new Dn(me, i, t, "").runParser(
          Number.POSITIVE_INFINITY,
          !1,
          !1,
          !1,
          !1
        ) && !t.cascadeParserHandler.invalid
      );
    }
    getConsumedOffset(e) {
      let t = Number.POSITIVE_INFINITY;
      for (let i = 0; i < e.positions.length; i++) {
        let n = e.positions[i].chunkPosition.primary,
          r = n.steps[0].node,
          s = n.offsetInNode,
          o = n.after,
          a = 0;
        for (; r.ownerDocument != this.xmldoc.document; )
          a++, (r = n.steps[a].node), (o = !1), (s = 0);
        let l = this.xmldoc.getNodeOffset(r, s, o);
        l < t && (t = l);
      }
      return t;
    }
    getPosition(e, t) {
      if (!e) return 0;
      let i = Number.POSITIVE_INFINITY;
      for (let n in this.primaryFlows) {
        let r = e.flowPositions[n];
        if (
          (!t &&
            (!r || 0 == r.positions.length) &&
            this.currentLayoutPosition &&
            (this.styler.styleUntilFlowIsReached(n),
            (r = this.currentLayoutPosition.flowPositions[n]),
            e != this.currentLayoutPosition &&
              r &&
              ((r = r.clone()), (e.flowPositions[n] = r))),
          r)
        ) {
          let e = this.getConsumedOffset(r);
          e < i && (i = e);
        }
      }
      return i;
    }
    dumpLocation(e) {
      V.debug("Location - page", this.currentLayoutPosition.page),
        V.debug("  current:", e),
        V.debug("  lookup:", this.lookupOffset);
      for (let e in this.currentLayoutPosition.flowPositions) {
        let t = this.currentLayoutPosition.flowPositions[e];
        for (let i of t.positions)
          V.debug("  Chunk", `${e}:`, i.flowChunk.startOffset);
      }
    }
    matchPageSide(e) {
      switch (e) {
        case "left":
        case "right":
        case "recto":
        case "verso":
          return new ee(this.style.pageScope, `${e}-page`).evaluate(this);
        default:
          return !0;
      }
    }
    updateStartSide(e) {
      for (let t in e.flowPositions) {
        let i = e.flowPositions[t];
        if (i && i.positions.length > 0) {
          let e = i.positions[0].flowChunk;
          if (this.getConsumedOffset(i) === e.startOffset) {
            let e = i.positions[0].flowChunk.breakBefore,
              t = i.startBreakType;
            i.startBreakType = Ic(Ve(t, e));
          }
        }
      }
    }
    selectPageMaster(e) {
      let t = this.currentLayoutPosition,
        i = this.getPosition(t);
      if (i == Number.POSITIVE_INFINITY) return null;
      let n,
        r = this.rootPageBoxInstance.children;
      for (let s = 0; s < r.length; s++) {
        if (((n = r[s]), n.pageBox.pseudoName === qa)) continue;
        let o = 1;
        n.pageBox.pseudoName === Mi &&
          (!this.actualPageWidth || !this.actualPageHeight) &&
          (o = 1 / 0);
        let a = n.getProp(this, "utilization");
        a && a.isNum() && (o = a.num);
        let l = this.queryUnitSize("em", !1),
          h = this.pageWidth() * this.pageHeight(),
          u = Math.ceil((o * h) / (l * l)),
          c = this.styler.cascade.currentPageType;
        (this.lookupOffset = this.styler.styleUntil(i, u)),
          (this.styler.cascade.currentPageType = c),
          this.updateStartSide(t),
          (this.layoutPositionAtPageStart = t.clone()),
          this.initLingering(),
          this.clearScope(this.style.pageScope);
        let d = n.getProp(this, "enabled");
        if (!d || d === b._true) {
          if (1 === t.page && this.blankPageAtStart) {
            n.style = {};
            let t = e.size;
            (e = {}), t && (e.size = t);
          }
          return this.pageManager.getPageRulePageMaster(n, e);
        }
      }
      throw new Error("No enabled page masters");
    }
    flowChunkIsAfterParentFlowForcedBreak(e) {
      let t = this.layoutPositionAtPageStart.flows,
        i = t[e.flowName].parentFlowName;
      if (i) {
        let n = e.startOffset,
          r = t[i].forcedBreakOffsets;
        if (!r.length || n < r[0]) return !1;
        let s = gt(r.length, (e) => r[e] > n) - 1,
          o = r[s],
          a = this.layoutPositionAtPageStart.flowPositions[i],
          l = this.getConsumedOffset(a);
        return !(o < l) && (l < o || !this.matchPageSide(a.startBreakType));
      }
      return !1;
    }
    setFormattingContextToColumn(e, t) {
      let i = this.currentLayoutPosition.flows[t];
      i.formattingContext || (i.formattingContext = new xo(null)),
        (e.flowRootFormattingContext = i.formattingContext);
    }
    layoutDeferredPageFloats(e) {
      let t = e.pageFloatLayoutContext,
        i = t.getDeferredPageFloatContinuations(),
        n = (e) => {
          if (e.floatReference !== le.PAGE) return !1;
          let t = this.layoutPositionAtPageStart.flowPositions.body,
            i = t && this.getConsumedOffset(t),
            n = this.xmldoc.getNodeOffset(e.nodePosition.steps[0].node, 0, !1);
          return null != n && null != i && n > i;
        },
        r = A("layoutDeferredPageFloats"),
        s = !1,
        o = 0;
      return (
        r
          .loopWithFrame((r) => {
            if (o === i.length) return void r.breakLoop();
            let a = i[o++],
              l = a.float;
            if (n(l)) return void r.breakLoop();
            let h = new zt().findByFloat(l),
              u = h.findPageFloatFragment(l, t);
            if (!u || !u.hasFloat(l))
              return t.isForbidden(l) || t.hasPrecedingFloatsDeferredToNext(l)
                ? (t.deferPageFloat(a), void r.breakLoop())
                : void e.layoutPageFloatInner(a, h, null, u).then((e) => {
                    if (!e) return void r.breakLoop();
                    let i = t.parent.isInvalidated();
                    i
                      ? r.breakLoop()
                      : (t.isInvalidated() && !i && ((s = !0), t.validate()),
                        r.continueLoop());
                  });
            r.continueLoop();
          })
          .then(() => {
            s && t.invalidate(), r.finish(!0);
          }),
        r.result()
      );
    }
    getLastAfterPositionIfDeferredFloatsExists(e, t) {
      if (
        e.pageFloatLayoutContext.getPageFloatContinuationsDeferredToNext()
          .length > 0
      ) {
        if (e.lastAfterPosition) {
          let i;
          return (
            t
              ? ((i = t.clone()), (i.primary = e.lastAfterPosition))
              : (i = new ht(e.lastAfterPosition)),
            i
          );
        }
        return null;
      }
      return null;
    }
    layoutColumn(e, t) {
      let i = this.currentLayoutPosition.flowPositions[t];
      if (!i || !this.matchPageSide(i.startBreakType)) return T(!0);
      this.setFormattingContextToColumn(e, t),
        e.init(),
        this.primaryFlows[t] && e.bands.length > 0 && (e.forceNonfitting = !1);
      let n = A("layoutColumn");
      return (
        this.layoutDeferredPageFloats(e).then(() => {
          if (e.pageFloatLayoutContext.isInvalidated())
            return void n.finish(!0);
          let r = [],
            s = [],
            o = !0;
          n.loopWithFrame((n) => {
            if (e.pageFloatLayoutContext.hasContinuingFloatFragmentsInFlow(t))
              n.breakLoop();
            else {
              for (; i.positions.length - s.length > 0; ) {
                let t = 0;
                for (; s.includes(t); ) t++;
                let a = i.positions[t];
                if (
                  a.flowChunk.startOffset > this.lookupOffset ||
                  this.flowChunkIsAfterParentFlowForcedBreak(a.flowChunk)
                )
                  break;
                for (let e = t + 1; e < i.positions.length; e++) {
                  if (s.includes(e)) continue;
                  let n = i.positions[e];
                  if (
                    n.flowChunk.startOffset > this.lookupOffset ||
                    this.flowChunkIsAfterParentFlowForcedBreak(n.flowChunk)
                  )
                    break;
                  n.flowChunk.isBetter(a.flowChunk) && ((a = n), (t = e));
                }
                let l = a.flowChunk,
                  h = !0;
                if (
                  (e.layout(a.chunkPosition, o, i.breakAfter).then((u) => {
                    if (e.pageFloatLayoutContext.isInvalidated()) n.breakLoop();
                    else {
                      if (
                        ((o = !1),
                        a.flowChunk.repeated &&
                          (null === u || l.exclusive) &&
                          r.push(t),
                        l.exclusive)
                      )
                        return s.push(t), void n.breakLoop();
                      {
                        let o = !!u || !!e.pageBreakType,
                          l = this.getLastAfterPositionIfDeferredFloatsExists(
                            e,
                            u
                          );
                        if (
                          (e.pageBreakType && l
                            ? ((a.chunkPosition = l),
                              (i.breakAfter = e.pageBreakType),
                              (e.pageBreakType = null))
                            : (s.push(t),
                              (u || l) &&
                                ((a.chunkPosition = u || l), r.push(t)),
                              (i.startBreakType = Ic(e.pageBreakType))),
                          o)
                        )
                          return void n.breakLoop();
                      }
                      (e.forceNonfitting = !1), h ? (h = !1) : n.continueLoop();
                    }
                  }),
                  h)
                )
                  return void (h = !1);
              }
              n.breakLoop();
            }
          }).then(() => {
            if (!e.pageFloatLayoutContext.isInvalidated()) {
              (i.positions = i.positions.filter(
                (e, t) => r.includes(t) || !s.includes(t)
              )),
                "column" === i.breakAfter && (i.breakAfter = null),
                e.saveDistanceToBlockEndFloats();
              let t = e.pageFloatLayoutContext.getMaxReachedAfterEdge();
              e.updateMaxReachedAfterEdge(t);
            }
            n.finish(!0);
          });
        }),
        n.result()
      );
    }
    createLayoutConstraint(e) {
      let t = this.currentLayoutPosition.page - 1,
        i = this.counterStore.createLayoutConstraint(t);
      return new wa([i].concat(e.getLayoutConstraints()));
    }
    createAndLayoutColumn(e, t, i, n, r, s, o, a, l, h, u, c, d, p) {
      let f,
        g = e.vertical
          ? e.isAutoWidth && e.isRightDependentOnAutoWidth
          : e.isAutoHeight && e.isTopDependentOnAutoHeight,
        m = r.element,
        b = new Cn(a, le.COLUMN, null, o, null, null, null),
        v = this.currentLayoutPosition.clone(),
        y = A("createAndLayoutColumn");
      return (
        y
          .loopWithFrame((e) => {
            let y = this.createLayoutConstraint(b);
            if (l > 1) {
              let e = this.viewport.document.createElement("div");
              if (
                (w(e, "position", "absolute"),
                m.appendChild(e),
                (f = new Os(e, d, this.clientLayout, y, b)),
                (f.forceNonfitting = p),
                (f.vertical = r.vertical),
                (f.rtl = r.rtl),
                (f.snapHeight = r.snapHeight),
                (f.snapWidth = r.snapWidth),
                r.vertical)
              ) {
                let e = (r.rtl ? l - s - 1 : s) * (u + h) + r.paddingTop,
                  t = parseFloat(m.style.width);
                f.setHorizontalPosition(r.paddingLeft + t - r.width, r.width),
                  f.setVerticalPosition(e, u);
              } else {
                let e = (r.rtl ? l - s - 1 : s) * (u + h) + r.paddingLeft;
                f.setVerticalPosition(r.paddingTop, r.height),
                  f.setHorizontalPosition(e, u);
              }
              (f.originX = t), (f.originY = i);
            } else (f = new Os(m, d, this.clientLayout, y, b)), f.copyFrom(r);
            (f.exclusions = g ? [] : n.concat()),
              (f.innerShape = c),
              b.setContainer(f),
              (f.vertical ? f.height : f.width) >= 0
                ? this.layoutColumn(f, o).then(() => {
                    b.isInvalidated() || b.finish(),
                      f.pageFloatLayoutContext.isInvalidated() &&
                      !a.isInvalidated()
                        ? (f.pageFloatLayoutContext.validate(),
                          (this.currentLayoutPosition = v.clone()),
                          f.element !== m && m.removeChild(f.element),
                          e.continueLoop())
                        : e.breakLoop();
                  })
                : (b.finish(), e.breakLoop());
          })
          .then(() => {
            y.finish(f);
          }),
        y.result()
      );
    }
    setPagePageFloatLayoutContextContainer(e, t, i) {
      (t instanceof Gi || (t instanceof Hs && !(t instanceof un))) &&
        e.setContainer(i);
    }
    getRegionPageFloatLayoutContext(e, t, i, n) {
      let r = t.getProp(this, "writing-mode") || null,
        s = t.getProp(this, "direction") || null;
      return new Cn(e, le.REGION, i, n, null, r, s);
    }
    layoutFlowColumnsWithBalancing(e, t, i, n, r, s, o, a, l) {
      let h = this.currentLayoutPosition.clone(),
        u = this.getRegionPageFloatLayoutContext(s, t, o, a),
        c = !0,
        d = () => (
          (this.currentLayoutPosition = h.clone()),
          this.layoutFlowColumns(e, t, i, n, r, s, u, o, a, l, c).thenAsync(
            (e) =>
              T(e ? { columns: e, position: this.currentLayoutPosition } : null)
          )
        );
      return d().thenAsync((e) => {
        if (!e) return T(null);
        if (l <= 1) return T(e.columns);
        let i = t.getProp(this, "column-fill") || b.balance,
          n = this.currentLayoutPosition.flowPositions[a],
          r = Yf(l, i, d, u, o, e.columns, n);
        return r
          ? ((c = !1),
            s.lock(),
            u.lock(),
            r
              .balanceColumns(e)
              .thenAsync(
                (e) => (
                  s.unlock(),
                  s.validate(),
                  u.unlock(),
                  (this.currentLayoutPosition = e.position),
                  T(e.columns)
                )
              ))
          : T(e.columns);
      });
    }
    layoutFlowColumns(e, t, i, n, r, s, o, a, l, h, u) {
      let c = A("layoutFlowColumns"),
        d = this.currentLayoutPosition.clone(),
        p = t.getPropAsNumber(this, "column-gap"),
        f =
          h > 1
            ? t.getPropAsNumber(this, "column-width")
            : a.vertical
            ? a.height
            : a.width,
        g = t.getActiveRegions(this),
        m = Vr(t.getProp(this, "shape-inside"), 0, 0, a.width, a.height, this),
        w = new Qa(
          l,
          this,
          this.viewport,
          this.styler,
          g,
          this.xmldoc,
          this.faces,
          this.style.footnoteProps,
          this,
          e,
          this.customRenderer,
          this.fallbackMap,
          this.documentURLTransformer
        ),
        b = 0,
        v = null,
        y = [];
      return (
        c
          .loopWithFrame((e) => {
            this.createAndLayoutColumn(
              t,
              i,
              n,
              r,
              a,
              b++,
              l,
              o,
              h,
              p,
              f,
              m,
              w,
              u
            ).then((t) =>
              s.isInvalidated()
                ? ((y = null), void e.breakLoop())
                : (((!!t.pageBreakType && "column" !== t.pageBreakType) ||
                    b === h) &&
                    !o.isInvalidated() &&
                    o.finish(),
                  o.isInvalidated()
                    ? ((b = 0),
                      (this.currentLayoutPosition = d.clone()),
                      o.validate(),
                      void (o.isLocked()
                        ? ((y = null), e.breakLoop())
                        : e.continueLoop()))
                    : ((v = t),
                      (y[b - 1] = v),
                      v.pageBreakType &&
                        "column" != v.pageBreakType &&
                        ((b = h),
                        "region" != v.pageBreakType &&
                          (this.pageBreaks[l] = !0)),
                      void (b < h ? e.continueLoop() : e.breakLoop())))
            );
          })
          .then(() => {
            c.finish(y);
          }),
        c.result()
      );
    }
    layoutContainer(e, t, i, n, r, s, o) {
      t.reset();
      let a = t.getProp(this, "enabled");
      if (a && a !== b._true) return T(!0);
      let l = A("layoutContainer"),
        h = t.getProp(this, "wrap-flow") === b.auto,
        u = t.getProp(this, "flow-from"),
        c = this.viewport.document.createElement("div");
      c.role = t instanceof Wi ? "complementary" : "presentation";
      let d = t.getProp(this, "position");
      w(c, "position", d ? d.name : "absolute");
      let p = t instanceof un;
      t instanceof cn ? i.appendChild(c) : i.insertBefore(c, i.firstChild);
      let f = new mo(c);
      (f.vertical = t.vertical),
        (f.rtl = t.rtl),
        (f.borderBoxSizing = t.borderBoxSizing),
        (f.exclusions = s),
        t.prepareContainer(this, f, e, this.faces, this.clientLayout),
        t instanceof un &&
          (f.width <= 0 || f.height <= 0) &&
          V.warn("Negative or zero page area size"),
        (f.originX = n),
        (f.originY = r),
        (n += f.left + f.marginLeft + f.borderLeft),
        (r += f.top + f.marginTop + f.borderTop),
        this.setPagePageFloatLayoutContextContainer(o, t, f);
      let g,
        m = !1;
      if (u && u.isIdent())
        if (this.pageBreaks[u.toString()])
          o.isInvalidated() ||
            t.finishContainer(
              this,
              f,
              e,
              null,
              1,
              this.clientLayout,
              this.faces
            ),
            (g = T(!0));
        else {
          let i = A("layoutContainer.inner"),
            a = u.toString(),
            l = t.getPropAsNumber(this, "column-count");
          this.layoutFlowColumnsWithBalancing(e, t, n, r, s, o, f, a, l).then(
            (n) => {
              if (!o.isInvalidated()) {
                let i = n[0];
                i.element === c && (f = i),
                  (f.computedBlockSize = Math.max.apply(
                    null,
                    n.map((e) => e.computedBlockSize)
                  )),
                  t.finishContainer(
                    this,
                    f,
                    e,
                    i,
                    l,
                    this.clientLayout,
                    this.faces
                  );
                let r = this.currentLayoutPosition.flowPositions[a];
                r && "region" === r.breakAfter && (r.breakAfter = null);
              }
              i.finish(!0);
            }
          ),
            (g = i.result());
        }
      else {
        let n = t.getProp(this, "content");
        if (
          n instanceof F &&
          n.expr instanceof Z &&
          n.expr.str.startsWith("running-element-")
        )
          n.visit(
            new mn(c, this, n, this.counterStore.getExprContentListener())
          );
        else if (n && St(n)) {
          let i = "span";
          n.url && (i = "img");
          let r = this.viewport.document.createElement(i);
          n.visit(
            new mn(r, this, n, this.counterStore.getExprContentListener())
          ),
            c.appendChild(r),
            "img" == i && t.transferSingleUriContentProps(this, r, this.faces),
            t.transferContentProps(this, f, e, this.faces),
            "span" == i &&
              Vf(
                r,
                t.getProp(this, "text-autospace"),
                t.getProp(this, "text-spacing-trim"),
                t.getProp(this, "hanging-punctuation"),
                this.lang,
                t.vertical
              );
        } else t.suppressEmptyBoxGeneration && (i.removeChild(c), (m = !0));
        m ||
          t.finishContainer(this, f, e, null, 1, this.clientLayout, this.faces),
          (g = T(!0));
      }
      return (
        g.then(() => {
          if (o.isInvalidated()) return void l.finish(!0);
          if (!t.isAutoHeight || Math.floor(f.computedBlockSize) > 0) {
            if (!m && !h) {
              let e = t.getProp(this, "shape-outside"),
                i = f.getOuterShape(e, this);
              s.push(i);
            }
          } else if (0 == t.children.length)
            return i.removeChild(c), void l.finish(!0);
          let a = p ? 0 : t.children.length - 1;
          l.loop(() => {
            for (; a >= 0 && a < t.children.length; ) {
              let i = t.children[p ? a++ : a--],
                l = this.layoutContainer(e, i, c, n, r, s, o);
              if (l.isPending())
                return l.thenAsync(() => T(!o.isInvalidated()));
              if (o.isInvalidated()) break;
            }
            return T(!1);
          }).then(() => {
            l.finish(!0);
          });
        }),
        l.result()
      );
    }
    processLinger() {
      let e = this.currentLayoutPosition.page;
      for (let t in this.currentLayoutPosition.flowPositions) {
        let i = this.currentLayoutPosition.flowPositions[t];
        for (let t = i.positions.length - 1; t >= 0; t--) {
          let n = i.positions[t];
          n.flowChunk.startPage >= 0 &&
            n.flowChunk.startPage + n.flowChunk.linger - 1 <= e &&
            i.positions.splice(t, 1);
        }
      }
    }
    initLingering() {
      let e = this.currentLayoutPosition.page;
      for (let t in this.currentLayoutPosition.flowPositions) {
        let i = this.currentLayoutPosition.flowPositions[t];
        for (let t = i.positions.length - 1; t >= 0; t--) {
          let n = i.positions[t];
          n.flowChunk.startPage < 0 &&
            n.flowChunk.startOffset < this.lookupOffset &&
            (n.flowChunk.startPage = e);
        }
      }
    }
    noMorePrimaryFlows(e) {
      for (let t in this.primaryFlows) {
        let i = e.flowPositions[t];
        if (i && i.positions.length > 0) return !1;
      }
      return !0;
    }
    layoutNextPage(e, t) {
      var i;
      let n = e.container === e.bleedBox;
      (this.pageBreaks = {}),
        t
          ? ((this.currentLayoutPosition = t.clone()),
            this.styler.replayFlowElementsFromOffset(t.highestSeenOffset))
          : ((this.currentLayoutPosition = new Jr()),
            this.styler.replayFlowElementsFromOffset(-1)),
        this.lang && e.bleedBox.setAttribute("lang", this.lang);
      if ((t = this.currentLayoutPosition).page > 1e4)
        throw new Error("Too many pages generated (over 10000 pages)");
      let r = t.startSideOfFlow("body");
      (t.isBlankPage = Mn(r) && this.matchPageSide(r)),
        (e.isBlankPage = t.isBlankPage),
        t.page++,
        null == e.pageType &&
          ((e.pageType =
            null !=
            (i = e.isBlankPage
              ? this.styler.cascade.previousPageType
              : this.styler.cascade.currentPageType)
              ? i
              : ""),
          (this.styler.cascade.previousPageType =
            this.styler.cascade.currentPageType)),
        this.clearScope(this.style.pageScope),
        (this.layoutPositionAtPageStart = t.clone());
      let s = n ? {} : this.pageManager.getCascadedPageStyle(e.pageType);
      if (
        (this.styler.cascade.applyVarFilter([s], this.styler, null),
        this.styler.cascade.applyCalcFilter(s, this.styler.context),
        !n)
      ) {
        let t = new ee(this.style.pageScope, "left-page");
        (e.side = t.evaluate(this) ? "left" : "right"),
          e.container.setAttribute("data-vivliostyle-page-side", e.side);
      }
      let o = this.selectPageMaster(s);
      if (!o) return T(null);
      let a = 0;
      if (!n) {
        e.setAutoPageWidth(o.pageBox.specified.width.value === Vn),
          e.setAutoPageHeight(o.pageBox.specified.height.value === Fn),
          this.counterStore.setCurrentPage(e),
          this.counterStore.updatePageCounters(s, this);
        let t = Cp($i(s), this);
        this.setPageSizeAndBleed(t, e),
          gg(s, t, e, this),
          (a = t.bleedOffset + t.bleed);
      }
      let l = (!n && o.getProp(this, "writing-mode")) || b.horizontal_tb;
      this.pageVertical = l != b.horizontal_tb;
      let h = o.getProp(this, "direction") || b.ltr,
        u = new Cn(
          this.rootPageFloatLayoutContext,
          le.PAGE,
          null,
          null,
          null,
          l,
          h
        ),
        c = A("layoutNextPage");
      return (
        c
          .loopWithFrame((t) => {
            this.layoutContainer(e, o, e.bleedBox, a, a, [], u).then(() => {
              u.isInvalidated() || u.finish(),
                u.isInvalidated()
                  ? ((this.currentLayoutPosition =
                      this.layoutPositionAtPageStart.clone()),
                    u.validate(),
                    t.continueLoop())
                  : t.breakLoop();
            });
          })
          .then(() => {
            o.adjustPageLayout(this, e, this.clientLayout),
              n ||
                (this.processLinger(),
                (t = this.currentLayoutPosition),
                Object.keys(t.flowPositions).forEach((e) => {
                  let i = t.flowPositions[e],
                    n = i.breakAfter;
                  n &&
                    ("page" === n || !this.matchPageSide(n)) &&
                    (i.breakAfter = null);
                })),
              (this.currentLayoutPosition = this.layoutPositionAtPageStart =
                null),
              (t.highestSeenOffset = this.styler.getReachedOffset());
            let i = this.style.store.getTriggersForDoc(this.xmldoc);
            e.finish(i, this.clientLayout),
              this.noMorePrimaryFlows(t) && (t = null),
              c.finish(t);
          }),
        c.result()
      );
    }
    setPageSizeAndBleed(e, t) {
      (this.actualPageWidth = e.pageWidth),
        (this.actualPageHeight = e.pageHeight),
        (this.pageSheetWidth = e.pageWidth + 2 * e.cropOffset),
        (this.pageSheetHeight = e.pageHeight + 2 * e.cropOffset),
        (t.container.style.width = `${this.pageSheetWidth}px`),
        (t.container.style.height = `${this.pageSheetHeight}px`),
        (t.bleedBox.style.left = `${e.bleedOffset}px`),
        (t.bleedBox.style.width = `${e.pageWidth}px`),
        (t.bleedBox.style.top = `${e.bleedOffset}px`),
        (t.bleedBox.style.height = `${e.pageHeight}px`),
        (t.bleedBox.style.padding = `${e.bleed}px`);
    }
  },
  wp = class e extends Nn {
    constructor(e, t, i, n) {
      super(e.rootScope, e, t, i, n, e.validatorSet, !i),
        (this.masterHandler = e),
        p(this, "insideRegion", !1);
    }
    startPageTemplateRule() {}
    startPageMasterRule(e, t, i) {
      let n = new Io(
        this.masterHandler.pageScope,
        e,
        t,
        i,
        this.masterHandler.rootBox,
        this.condition,
        this.owner.getBaseSpecificity()
      );
      this.masterHandler.pushHandler(
        new $a(n.scope, this.masterHandler, n, this.validatorSet)
      );
    }
    startWhenRule(t) {
      let i = t.expr;
      null != this.condition && (i = tt(this.scope, this.condition, i)),
        this.masterHandler.pushHandler(
          new e(this.masterHandler, i, this, this.regionId)
        );
    }
    startDefineRule() {
      this.masterHandler.pushHandler(new ga(this.scope, this.owner));
    }
    startFontFaceRule() {
      let e = {};
      this.masterHandler.fontFaces.push({
        properties: e,
        condition: this.condition,
      }),
        this.masterHandler.pushHandler(
          new Vs(
            this.scope,
            this.owner,
            null,
            e,
            this.masterHandler.validatorSet,
            "font-face"
          )
        );
    }
    startFlowRule(e) {
      let t = this.masterHandler.flowProps[e];
      t || ((t = {}), (this.masterHandler.flowProps[e] = t)),
        this.masterHandler.pushHandler(
          new Vs(
            this.scope,
            this.owner,
            null,
            t,
            this.masterHandler.validatorSet
          )
        );
    }
    startViewportRule() {
      let e = {};
      this.masterHandler.viewportProps.push(e),
        this.masterHandler.pushHandler(
          new Vs(
            this.scope,
            this.owner,
            this.condition,
            e,
            this.masterHandler.validatorSet
          )
        );
    }
    startFootnoteRule(e) {
      let t = this.masterHandler.footnoteProps;
      if (e) {
        let i = wo(t, "_pseudos");
        (t = i[e]), t || ((t = {}), (i[e] = t));
      }
      this.masterHandler.pushHandler(
        new Vs(this.scope, this.owner, null, t, this.masterHandler.validatorSet)
      );
    }
    startRegionRule() {
      (this.insideRegion = !0), this.startSelectorRule();
    }
    startPageRule() {
      let e = new Ya(
        this.masterHandler.pageScope,
        this.masterHandler,
        this,
        this.validatorSet,
        this.masterHandler.pageProps
      );
      this.masterHandler.pushHandler(e), e.startPageRule();
    }
    startRuleBody() {
      if ((Nn.prototype.startRuleBody.call(this), this.insideRegion)) {
        this.insideRegion = !1;
        let t = "R" + this.masterHandler.regionCount++;
        this.special("region-id", L(t)), this.endRule();
        let i = new e(this.masterHandler, this.condition, this, t);
        this.masterHandler.pushHandler(i), i.startRuleBody();
      }
    }
  },
  sl = class extends Dr {
    constructor(e) {
      super(),
        (this.validatorSet = e),
        p(this, "rootScope"),
        p(this, "pageScope"),
        p(this, "rootBox"),
        p(this, "cascadeParserHandler"),
        p(this, "regionCount", 0),
        p(this, "fontFaces", []),
        p(this, "footnoteProps", {}),
        p(this, "flowProps", {}),
        p(this, "viewportProps", []),
        p(this, "pageProps", {}),
        (this.rootScope = new us(null)),
        (this.pageScope = new us(this.rootScope)),
        (this.rootBox = new Ua(this.rootScope)),
        (this.cascadeParserHandler = new wp(this, null, null, null)),
        (this.slave = this.cascadeParserHandler);
    }
  };
function vb(e, t) {
  return t.parseOPSResource(e);
}
var ol = class extends Cs {
    constructor(e) {
      super(vb, "document"),
        (this.fontDeobfuscator = e),
        p(this, "styleByKey", {}),
        p(this, "styleFetcherByKey", {}),
        p(this, "styleByDocURL", {}),
        p(this, "triggersByDocURL", {}),
        p(this, "validatorSet", null),
        p(this, "styleSheets", []),
        p(this, "triggerSingleDocumentPreprocessing", !1);
    }
    init(e, t) {
      this.setStyleSheets(e, t);
      let i = A("OPSDocStore.init");
      return (
        (this.validatorSet = Yd()),
        Nb().then(() => {
          (this.triggerSingleDocumentPreprocessing = !0), i.finish(!0);
        }),
        i.result()
      );
    }
    getStyleForDoc(e) {
      return this.styleByDocURL[e.url];
    }
    getTriggersForDoc(e) {
      return this.triggersByDocURL[e.url];
    }
    setStyleSheets(e, t) {
      this.clearStyleSheets(),
        e && e.forEach(this.addAuthorStyleSheet, this),
        t && t.forEach(this.addUserStyleSheet, this);
    }
    clearStyleSheets() {
      this.styleSheets.splice(0);
    }
    addAuthorStyleSheet(e) {
      let t = e.url;
      t && (t = J(An(t), dn)),
        this.styleSheets.push({
          url: t,
          text: e.text,
          flavor: "Author",
          classes: null,
          media: null,
        });
    }
    addUserStyleSheet(e) {
      let t = e.url;
      t && (t = J(An(t), dn)),
        this.styleSheets.push({
          url: t,
          text: e.text,
          flavor: "User",
          classes: null,
          media: null,
        });
    }
    parseOPSResource(e) {
      let t = A("OPSDocStore.load"),
        i = e.url,
        n = i.endsWith("?viv-toc-box");
      return (
        Np(e, this).then((e) => {
          var r;
          if (!e) return void t.finish(null);
          if (this.triggerSingleDocumentPreprocessing) {
            let t = Ge("PREPROCESS_SINGLE_DOCUMENT");
            for (let i = 0; i < t.length; i++)
              try {
                t[i](e.document);
              } catch (e) {
                V.warn("Error during single document preprocessing:", e);
              }
          }
          let s = [],
            o = e.document.getElementsByTagNameNS(
              "http://www.idpf.org/2007/ops",
              "trigger"
            );
          for (let e = 0; e < o.length; e++) {
            let t = o[e],
              i = t.getAttributeNS(
                "http://www.w3.org/2001/xml-events",
                "observer"
              ),
              n = t.getAttributeNS(
                "http://www.w3.org/2001/xml-events",
                "event"
              ),
              r = t.getAttribute("action"),
              a = t.getAttribute("ref");
            i &&
              n &&
              r &&
              a &&
              s.push({ observer: i, event: n, action: r, ref: a });
          }
          this.triggersByDocURL[i] = s;
          let a = [];
          if (
            (a.push({
              url: J("user-agent-page.css", qt),
              text: yc,
              flavor: "UA",
              classes: null,
              media: null,
            }),
            n)
          )
            a.push({
              url: J("user-agent-toc.css", qt),
              text: Sc,
              flavor: "UA",
              classes: null,
              media: null,
            });
          else {
            let t = e.document.querySelectorAll("style, link, meta");
            for (let e of t) {
              let t = e.namespaceURI,
                n = e.localName;
              if ("http://www.w3.org/1999/xhtml" == t)
                if ("style" == n) {
                  let t = e.getAttribute("class"),
                    n = e.getAttribute("media"),
                    r = e.getAttribute("title");
                  a.push({
                    url: i,
                    text: e.textContent,
                    flavor: "Author",
                    classes: r ? t : null,
                    media: n,
                  });
                } else if ("link" == n) {
                  let t = e.getAttribute("href"),
                    n =
                      null == (r = e.getAttribute("rel"))
                        ? void 0
                        : r.split(/\s+/),
                    s = e.getAttribute("class"),
                    o = e.getAttribute("media");
                  if (
                    t &&
                    null != n &&
                    n.includes("stylesheet") &&
                    (!n.includes("alternate") || s)
                  ) {
                    let n = J(t, i),
                      r = e.getAttribute("title");
                    a.push({
                      url: n,
                      text: null,
                      classes: r ? s : null,
                      media: o,
                      flavor: "Author",
                    });
                  }
                } else
                  "meta" == n &&
                    "viewport" == e.getAttribute("name") &&
                    a.push({
                      url: i,
                      text: this.processViewportMeta(e),
                      flavor: "Author",
                      classes: null,
                      media: null,
                    });
            }
            for (let e = 0; e < this.styleSheets.length; e++)
              a.push(this.styleSheets[e]);
          }
          let l = "";
          for (let e = 0; e < a.length; e++)
            (l += a[e].url),
              (l += "^"),
              a[e].text && (l += a[e].text),
              (l += "^");
          let h = this.styleByKey[l];
          if (h) return (this.styleByDocURL[i] = h), void t.finish(e);
          let u = this.styleFetcherByKey[l];
          u ||
            ((u = new tn(() => {
              let e = A("fetchStylesheet"),
                t = 0,
                i = new sl(this.validatorSet);
              return (
                e
                  .loop(() => {
                    if (t < a.length) {
                      let e = a[t++];
                      return (
                        i.startStylesheet(e.flavor),
                        null !== e.text
                          ? zr(e.text, i, e.url, e.classes, e.media).thenReturn(
                              !0
                            )
                          : wc(e.url, i, e.classes, e.media)
                      );
                    }
                    return T(!1);
                  })
                  .then(() => {
                    let t = i.cascadeParserHandler.finish();
                    (h = new Tp(
                      this,
                      i.rootScope,
                      i.pageScope,
                      t,
                      i.rootBox,
                      i.fontFaces,
                      i.footnoteProps,
                      i.flowProps,
                      i.viewportProps,
                      i.pageProps
                    )),
                      (this.styleByKey[l] = h),
                      delete this.styleFetcherByKey[l],
                      e.finish(h);
                  }),
                e.result()
              );
            }, `FetchStylesheet ${i}`)),
            (this.styleFetcherByKey[l] = u),
            u.start()),
            u.get().then((n) => {
              (this.styleByDocURL[i] = n), t.finish(e);
            });
        }),
        t.result()
      );
    }
    processViewportMeta(e) {
      return "";
    }
  },
  Yi = "▸",
  Tg = "▾",
  Tb = "▹";
function rl(e) {
  let t = Array.from(e.querySelectorAll("nav[*|type],nav[epub\\:type]"));
  if (
    t.length > 0 ||
    ((t = Array.from(e.querySelectorAll("[role=doc-toc]"))), t.length > 0)
  )
    return t;
  for (let i of e.querySelectorAll(
    "nav,.toc,#toc,#table-of-contents,#contents,[role=directory]"
  )) {
    if (t.find((e) => e.contains(i))) continue;
    let e = i;
    /^h[1-6]$/.test(e.localName) &&
      (e = e.previousElementSibling ? e.nextElementSibling : e.parentElement),
      e && e.querySelector("li a[href]") && t.push(e);
  }
  return t;
}
function wg(e) {
  let t = rl(e),
    i = [];
  for (let e of t) for (let t of e.querySelectorAll("li a[href]")) i.push(t);
  return i;
}
var il = class {
  constructor(e, t, i, n, r, s, o, a, l, h) {
    (this.store = e),
      (this.url = t),
      (this.lang = i),
      (this.clientLayout = n),
      (this.fontMapper = r),
      (this.rendererFactory = o),
      (this.fallbackMap = a),
      (this.documentURLTransformer = l),
      (this.counterStore = h),
      p(this, "pref"),
      p(this, "page", null),
      p(this, "instance", null),
      (this.pref = vr(s)),
      (this.pref.spreadView = !1);
  }
  setAutoHeight(e, t) {
    if (0 != t--)
      for (let i = e.firstChild; i; i = i.nextSibling)
        if (1 == i.nodeType) {
          let e = i;
          "auto" != Bt(e, "height", "auto") &&
            (w(e, "height", "auto"), this.setAutoHeight(e, t)),
            "absolute" == Bt(e, "position", "static") &&
              (w(e, "position", "relative"), this.setAutoHeight(e, t));
        }
  }
  makeCustomRenderer(e) {
    let t = this.rendererFactory.makeCustomRenderer(e);
    return (e, i, n) => {
      let r = n.behavior;
      if (r)
        switch (r.toString()) {
          case "toc-node-anchor":
            (n.color = b.inherit), (n["text-decoration"] = b.none);
            break;
          case "toc-node":
            (n.display = b.block),
              (n.margin = ne),
              (n.padding = ne),
              (n["padding-inline-start"] = new P(1.25, "em"));
            break;
          case "toc-node-first-child":
            (n.display = b.inline_block),
              (n.margin = new P(0.2, "em")),
              (n["vertical-align"] = b.top),
              (n.color = b.inherit),
              (n["text-decoration"] = b.none);
            break;
          case "toc-container":
            n.padding = ne;
        }
      if (!r || ("toc-node" != r.toString() && "toc-container" != r.toString()))
        return t(e, i, n);
      let s = e.firstChild;
      s &&
        1 !== s.nodeType &&
        de(s) &&
        e.replaceChild(e.ownerDocument.createComment(s.textContent), s);
      let o = i.getAttribute("data-adapt-class");
      if ("toc-node" == o) {
        let e = i.firstChild;
        e.textContent != Yi &&
          ((e.textContent = Yi),
          w(e, "cursor", "pointer"),
          e.addEventListener("click", wb, !1),
          e.setAttribute("role", "button"),
          e.setAttribute("aria-expanded", "false"),
          i.setAttribute("aria-expanded", "false"),
          "0px" !== i.style.height && (e.tabIndex = 0));
      }
      let a = i.ownerDocument.createElement("div");
      if (
        (a.setAttribute("data-adapt-process-children", "true"),
        "toc-node" == r.toString())
      ) {
        let t = i.ownerDocument.createElement("div");
        if (
          ((t.textContent = Tb),
          w(t, "margin", "0.2em 0 0 -1em"),
          w(t, "margin-inline-start", "-1em"),
          w(t, "margin-inline-end", "0"),
          w(t, "display", "inline-block"),
          w(t, "width", "1em"),
          w(t, "text-align", "center"),
          w(t, "vertical-align", "top"),
          w(t, "cursor", "default"),
          w(t, "font-family", "Menlo,sans-serif"),
          a.appendChild(t),
          w(a, "overflow", "hidden"),
          a.setAttribute("data-adapt-class", "toc-node"),
          a.setAttribute("role", "treeitem"),
          "toc-node" == o || "toc-container" == o)
        ) {
          w(a, "height", "0px");
          let t = e.firstElementChild;
          t && "a" === t.localName && (t.tabIndex = -1);
        } else i.setAttribute("role", "tree");
      } else
        "toc-node" == o &&
          (a.setAttribute("data-adapt-class", "toc-container"),
          a.setAttribute("role", "group"),
          a.setAttribute("aria-hidden", "true"));
      return T(a);
    };
  }
  showTOC(e, t, i, n, r) {
    if (this.page) return T(this.page);
    let s = A("showTOC"),
      o = new go(e, e);
    this.page = o;
    let a = kt(this.url) + "?viv-toc-box";
    return (
      this.store.load(a).then((n) => {
        for (let e of rl(n.document))
          e.setAttribute("data-vivliostyle-role", "doc-toc");
        let a = this.store.getStyleForDoc(n),
          l = a.sizeViewport(i, 1e5, r);
        t = new wn(t.window, l.fontSize, 0, t.root, l.width, l.height);
        let h = this.makeCustomRenderer(n),
          u = new ss(
            a,
            n,
            this.lang,
            t,
            this.clientLayout,
            this.fontMapper,
            h,
            this.fallbackMap,
            0,
            this.documentURLTransformer,
            this.counterStore
          );
        (this.instance = u),
          (u.pref = this.pref),
          u.init().then(() => {
            u.layoutNextPage(o, null).then(() => {
              this.setAutoHeight(e, 2), s.finish(o);
            });
          });
      }),
      s.result()
    );
  }
  hideTOC() {
    this.page &&
      ((this.page.container.style.visibility = "hidden"),
      this.page.container.setAttribute("aria-hidden", "true"));
  }
  isTOCVisible() {
    return !!this.page && "visible" === this.page.container.style.visibility;
  }
  getTOC() {
    if (!this.page) return [];
    function e(e) {
      if (!e) return [];
      let i = e.querySelectorAll(":scope > [role=treeitem] > a[href]");
      return Array.from(i).map(t);
    }
    function t(t) {
      let i = new URL(t.href),
        [, n] = i.hash.match(/^#?(.*)$/);
      return {
        id: n,
        title: t.innerText,
        children: e(t.parentElement.querySelector("[role=group]")),
      };
    }
    return e(this.page.container.querySelector("[role=tree]"));
  }
};
function wb(e) {
  let t = e.target,
    i = t.textContent == Yi;
  t.textContent = i ? Tg : Yi;
  let n = t.parentNode;
  t.setAttribute("aria-expanded", i ? "true" : "false"),
    n.setAttribute("aria-expanded", i ? "true" : "false");
  let r = n.firstChild;
  for (; r; ) {
    if (1 === r.nodeType) {
      let e = r,
        t = e.getAttribute("data-adapt-class");
      if ("toc-container" === t) {
        if (
          (e.setAttribute("aria-hidden", i ? "false" : "true"), e.firstChild)
        ) {
          r = e.firstChild;
          continue;
        }
      } else if (
        "toc-node" === t &&
        ((e.style.height = i ? "auto" : "0px"),
        e.children.length >= 2 && (e.children[1].tabIndex = i ? 0 : -1),
        e.children.length >= 3 && ((e.children[0].tabIndex = i ? 0 : -1), !i))
      ) {
        let t = e.children[0];
        if (t.textContent == Tg) {
          (t.textContent = Yi),
            t.setAttribute("aria-expanded", "false"),
            e.setAttribute("aria-expanded", "false"),
            (r = e.children[2]);
          continue;
        }
      }
    }
    for (; !r.nextSibling && r.parentNode !== n; ) r = r.parentNode;
    r = r.nextSibling;
  }
  e.stopPropagation();
}
var Ki = class extends ol {
    constructor() {
      super(null),
        p(this, "plainXMLStore"),
        p(this, "jsonStore"),
        p(this, "opfByURL", {}),
        p(this, "primaryOPFByEPubURL", {}),
        p(this, "deobfuscators", {}),
        p(this, "documents", {}),
        (this.fontDeobfuscator = this.makeDeobfuscatorFactory()),
        (this.plainXMLStore = Sg()),
        (this.jsonStore = yh());
    }
    makeDeobfuscatorFactory() {
      return (e) => this.deobfuscators[e];
    }
    loadAsPlainXML(e, t, i) {
      return this.plainXMLStore.load(e, t, i);
    }
    startLoadingAsPlainXML(e) {
      this.plainXMLStore.fetch(e);
    }
    loadAsJSON(e, t, i) {
      return this.jsonStore.load(e, t, i);
    }
    loadPubDoc(e) {
      let t = A("loadPubDoc");
      return (
        bs(e, null, "HEAD").then((i) => {
          if (i.status >= 400)
            this.loadEPUBDoc(e).then((n) => {
              n
                ? t.finish(n)
                : (V.error(
                    `Failed to fetch a source document from ${e} (${i.status}${
                      i.statusText ? " " + i.statusText : ""
                    })`
                  ),
                  t.finish(null));
            });
          else if (
            (!i.status &&
              !i.responseXML &&
              !i.responseText &&
              !i.responseBlob &&
              !i.contentType &&
              /\/[^/.]+(?:[#?]|$)/.test(e) &&
              (e = e.replace(/([#?]|$)/, "/$1")),
            "application/oebps-package+xml" == i.contentType ||
              /\.opf(?:[#?]|$)/.test(e))
          ) {
            let [, i, n] = e.match(/^((?:.*\/)?)([^/]*)$/);
            this.loadOPF(i, n).thenFinish(t);
          } else
            "application/ld+json" == i.contentType ||
            "application/webpub+json" == i.contentType ||
            "application/audiobook+json" == i.contentType ||
            "application/json" == i.contentType ||
            /\.json(?:ld)?(?:[#?]|$)/.test(e)
              ? this.loadAsJSON(e, !0).then((i) => {
                  if (!i) return this.reportLoadError(e), void t.finish(null);
                  let n = new Gs(this, e);
                  n.initWithWebPubManifest(i, void 0, e).then(() => {
                    t.finish(n);
                  });
                })
              : this.loadWebPub(e).then((i) => {
                  i
                    ? t.finish(i)
                    : this.loadEPUBDoc(e).then((i) => {
                        i
                          ? t.finish(i)
                          : (V.error(`Failed to load ${e}.`), t.finish(null));
                      });
                });
        }),
        t.result()
      );
    }
    loadEPUBDoc(e) {
      let t = A("loadEPUBDoc");
      e.endsWith("/") || (e += "/"),
        this.startLoadingAsPlainXML(e + "META-INF/encryption.xml");
      let i = e + "META-INF/container.xml";
      return (
        this.loadAsPlainXML(i).then((i) => {
          if (i) {
            let n = i
              .doc()
              .child("container")
              .child("rootfiles")
              .child("rootfile")
              .attribute("full-path");
            for (let i of n)
              if (i) return void this.loadOPF(e, i).thenFinish(t);
          }
          t.finish(null);
        }),
        t.result()
      );
    }
    loadOPF(e, t) {
      let i = e + t,
        n = this.opfByURL[i];
      if (n) return T(n);
      let r = A("loadOPF");
      return (
        this.loadAsPlainXML(i, !0, `Failed to fetch EPUB OPF ${i}`).then(
          (t) => {
            t
              ? ((n = new Gs(this, e)),
                (this.opfByURL[i] = n),
                (this.primaryOPFByEPubURL[e] = n),
                this.plainXMLStore.resources[e + "META-INF/container.xml"]
                  ? this.loadAsPlainXML(e + "META-INF/encryption.xml").then(
                      (e) => {
                        n.initWithXMLDoc(t, e).then(() => {
                          r.finish(n);
                        });
                      }
                    )
                  : n.initWithXMLDoc(t, null).then(() => {
                      r.finish(n);
                    }))
              : this.reportLoadError(i);
          }
        ),
        r.result()
      );
    }
    loadWebPub(e) {
      let t = A("loadWebPub");
      return (
        this.load(e).then((i) => {
          if (i)
            if (
              i.document.querySelector(
                "a[href='META-INF/'],a[href$='/META-INF/']"
              )
            )
              t.finish(null);
            else {
              let n = i.document,
                r = new Gs(this, e),
                s = n.querySelector(
                  "link[rel='publication'],link[rel='manifest'][type='application/webpub+json']"
                );
              if (s) {
                let i = s.getAttribute("href");
                if (/^#/.test(i)) {
                  let e = Xs(n.getElementById(i.substr(1)).textContent);
                  r.initWithWebPubManifest(e, n).then(() => {
                    t.finish(r);
                  });
                } else {
                  let i = J(s.getAttribute("href"), e);
                  this.loadAsJSON(
                    i,
                    !0,
                    `Failed to fetch Publication Manifest ${i}`
                  ).then((e) => {
                    r.initWithWebPubManifest(e, n, i).then(() => {
                      t.finish(r);
                    });
                  });
                }
              } else
                r.initWithWebPubManifest({}, n).then(() => {
                  r.toc &&
                    r.toc.src === i.url &&
                    0 === rl(n).length &&
                    (r.toc = null),
                    t.finish(r);
                });
            }
          else this.reportLoadError(e);
        }),
        t.result()
      );
    }
    addDocument(e, t) {
      let i = A("EPUBDocStore.load"),
        n = kt(e);
      return (
        (this.documents[n] = this.parseOPSResource({
          status: 200,
          statusText: "",
          url: n,
          contentType: t.contentType,
          responseText: null,
          responseXML: t,
          responseBlob: null,
        })).thenFinish(i),
        i.result()
      );
    }
    reportLoadError(e) {
      let t = (e) => e.replace(/([^:/?#]|^)[/?#].*/, "$1");
      e.startsWith("data:")
        ? V.error(`Failed to load ${e}. Invalid data.`)
        : e.startsWith("http:") && dn.startsWith("https:")
        ? V.error(
            `Failed to load ${e}. Mixed Content ("http:" content on "https:" context) is not allowed.`
          )
        : (() => {
            let i = t(e);
            return !(
              i === t(dn) ||
              Object.keys(this.resources).find(
                (e) => this.resources[e] && t(e) === i
              ) ||
              !/^https?:?\/\/[^/]/.test(e) ||
              /\.(xhtml|xht|xml|opf)$/i.test(e)
            );
          })()
        ? V.error(
            `Failed to load ${e}. This may be caused by the server not allowing cross-origin resource sharing (CORS).`
          )
        : V.error(`Failed to load ${e}. The target resource is invalid.`);
    }
    load(e) {
      let t = kt(e),
        i = this.documents[t];
      if (i) return i.isPending() ? i : T(i.get());
      {
        let e = A("EPUBDocStore.load");
        return (
          (i = super.load(
            t,
            !0,
            `Failed to fetch a source document from ${t}`
          )),
          i.then((i) => {
            i ? e.finish(i) : this.reportLoadError(t);
          }),
          e.result()
        );
      }
    }
    processViewportMeta(e) {
      let t = e.getAttribute("content");
      if (!t) return "";
      let i,
        n = {};
      for (
        ;
        null !=
        (i = t.match(
          /^,?\s*([-A-Za-z_.][-A-Za-z_0-9.]*)\s*=\s*([-+A-Za-z_0-9.]*)\s*/
        ));

      )
        (t = t.substr(i[0].length)), (n[i[1]] = i[2]);
      let r = n.width - 0,
        s = n.height - 0;
      if (r && s) {
        return (
          `@-epubx-viewport{width:${r}px;height:${s}px;}` +
          (!!Object.values(this.primaryOPFByEPubURL).find((e) => e.prePaginated)
            ? `@page{size:${r}px ${s}px;margin:0;}`
            : "@page{margin:0;}")
        );
      }
      return "";
    }
  },
  ll = class {
    constructor() {
      p(this, "id", null),
        p(this, "src", ""),
        p(this, "mediaType", null),
        p(this, "title", null),
        p(this, "itemRefElement", null),
        p(this, "spineIndex", -1),
        p(this, "compressedSize", 0),
        p(this, "compressed", null),
        p(this, "epage", 0),
        p(this, "epageCount", 0),
        p(this, "startPage", null),
        p(this, "skipPagesBefore", null),
        p(this, "itemProperties"),
        (this.itemProperties = Hp);
    }
    initWithElement(e, t) {
      (this.id = e.getAttribute("id")),
        (this.src = J(e.getAttribute("href"), t)),
        (this.mediaType = e.getAttribute("media-type"));
      let i = e.getAttribute("properties");
      i && (this.itemProperties = Wp(i.split(/\s+/)));
    }
    initWithParam(e) {
      (this.spineIndex = e.index),
        (this.id = `item${e.index + 1}`),
        (this.src = e.url),
        (this.startPage = e.startPage),
        (this.skipPagesBefore = e.skipPagesBefore);
    }
  };
function kb(e) {
  return e.id;
}
function Ab(e) {
  return (t) => {
    let i = A("deobfuscator");
    return (
      Lb("SHA-1", e).then((e) => {
        let n = t.slice(0, 1040),
          r = t.slice(1040, t.size);
        bh(n).then((t) => {
          let n = new DataView(t);
          for (let t = 0; t < n.byteLength; t++) {
            let i = n.getUint8(t);
            (i ^= e[t % 20]), n.setUint8(t, i);
          }
          i.finish(vc([n, r]));
        });
      }),
      i.result()
    );
  };
}
function Lb(e, t) {
  let i = A("makeDigest"),
    n = i.suspend();
  return (
    window.crypto.subtle.digest(e, new TextEncoder().encode(t)).then((e) => {
      n.schedule(new Uint8Array(e));
    }),
    i.result()
  );
}
var os = {
    dcterms: "http://purl.org/dc/terms/",
    marc: "http://id.loc.gov/vocabulary/",
    media: "http://www.idpf.org/epub/vocab/overlays/#",
    rendition: "http://www.idpf.org/vocab/rendition/#",
    onix: "http://www.editeur.org/ONIX/book/codelists/current.html#",
    xsd: "http://www.w3.org/2001/XMLSchema#",
    opf: "http://www.idpf.org/2007/opf",
  },
  qi = "http://idpf.org/epub/vocab/package/meta/#",
  ve = {
    language: `${os.dcterms}language`,
    title: `${os.dcterms}title`,
    creator: `${os.dcterms}creator`,
    layout: `${os.rendition}layout`,
    titleType: `${qi}title-type`,
    displaySeq: `${qi}display-seq`,
    alternateScript: `${qi}alternate-script`,
    role: `${qi}role`,
  };
function Rb(e, t) {
  let i = {};
  return (n, r) => {
    var s, o, a, l, h, u;
    let c,
      d,
      p = n.r || i,
      f = r.r || i;
    if (
      e == ve.title &&
      ((c = "main" == (null == (s = p[ve.titleType]) ? void 0 : s[0].v)),
      (d = "main" == (null == (o = f[ve.titleType]) ? void 0 : o[0].v)),
      c != d)
    )
      return c ? -1 : 1;
    let g = parseInt(null == (a = p[ve.displaySeq]) ? void 0 : a[0].v, 10);
    isNaN(g) && (g = Number.MAX_VALUE);
    let m = parseInt(null == (l = f[ve.displaySeq]) ? void 0 : l[0].v, 10);
    return (
      isNaN(m) && (m = Number.MAX_VALUE),
      g != m
        ? g - m
        : e != ve.language &&
          t &&
          ((c =
            (null == (h = p[ve.language] || p[ve.alternateScript])
              ? void 0
              : h[0].v) == t),
          (d =
            (null == (u = f[ve.language] || f[ve.alternateScript])
              ? void 0
              : u[0].v) == t),
          c != d)
        ? c
          ? -1
          : 1
        : n.o - r.o
    );
  };
}
function Ib(e, t) {
  let i;
  if (t) {
    i = {};
    for (let e in os) i[e] = os[e];
    let e;
    for (
      ;
      null !=
      (e = t.match(
        /^\s*([A-Z_a-z\u007F-\uFFFF][-.A-Z_a-z0-9\u007F-\uFFFF]*):\s*(\S+)/
      ));

    )
      (t = t.substr(e[0].length)), (i[e[1]] = e[2]);
  } else i = os;
  let n = (e) => {
      if (e) {
        let t = e.match(/^\s*(([^:]*):)?(\S+)\s*$/);
        if (t) {
          let e = t[2] ? i[t[2]] : qi;
          if (e) return e + t[3];
        }
      }
      return null;
    },
    r = 1,
    s = e.childElements().forEachNonNull((e) => {
      if ("meta" == e.localName) {
        let t = n(e.getAttribute("property"));
        if (t)
          return {
            name: t,
            value: e.textContent,
            id: e.getAttribute("id"),
            order: r++,
            refines: e.getAttribute("refines"),
            lang: null,
            scheme: n(e.getAttribute("scheme")),
            role: null,
          };
      } else if ("http://purl.org/dc/elements/1.1/" == e.namespaceURI)
        return {
          name: os.dcterms + e.localName,
          order: r++,
          lang: e.getAttribute("xml:lang"),
          value: e.textContent,
          id: e.getAttribute("id"),
          refines: null,
          scheme: null,
          role: e.getAttribute("role") || e.getAttribute("opf:role"),
        };
      return null;
    }),
    o = ar(s, (e) => e.refines),
    a = (e) =>
      $p(e, (e, t) =>
        e.map((e) => {
          let t = { v: e.value, o: e.order };
          e.scheme && (t.s = e.scheme);
          let i = o[`#${e.id}`] || [];
          if (i.length || e.lang || e.role) {
            e.lang &&
              i.push({
                name: ve.language,
                value: e.lang,
                lang: null,
                id: null,
                refines: e.id,
                scheme: null,
                order: e.order,
                role: null,
              }),
              e.role &&
                i.push({
                  name: ve.role,
                  value: e.role,
                  lang: null,
                  id: null,
                  refines: e.id,
                  scheme: null,
                  order: e.order,
                  role: null,
                });
            let n = ar(i, (e) => e.name);
            t.r = a(n);
          }
          return t;
        })
      ),
    l = a(ar(s, (e) => (e.refines ? null : e.name))),
    h = null;
  l[ve.language] && (h = l[ve.language][0].v);
  let u = (e) => {
    for (let t in e) {
      let i = e[t];
      i.sort(Rb(t, h));
      for (let e = 0; e < i.length; e++) {
        let t = i[e].r;
        t && u(t);
      }
    }
  };
  return u(l), l;
}
function Vb() {
  let e = window.MathJax;
  return e ? e.Hub : null;
}
var Pg = {
    "application/xhtml+xml": !0,
    "image/jpeg": !0,
    "image/png": !0,
    "image/svg+xml": !0,
    "image/gif": !0,
    "audio/mp3": !0,
  },
  Pp = "viv-id-",
  Gs = class {
    constructor(e, t) {
      (this.store = e),
        (this.pubURL = t),
        p(this, "opfXML", null),
        p(this, "encXML", null),
        p(this, "items", null),
        p(this, "spine", null),
        p(this, "itemMap", null),
        p(this, "itemMapByPath", null),
        p(this, "uid", null),
        p(this, "bindings", {}),
        p(this, "lang", null),
        p(this, "epageCount", 0),
        p(this, "prePaginated", !1),
        p(this, "epageIsRenderedPage", !0),
        p(this, "epageCountCallback", null),
        p(this, "metadata", {}),
        p(this, "toc", null),
        p(this, "cover", null),
        p(this, "fallbackMap", {}),
        p(this, "pageProgression", null),
        p(this, "documentURLTransformer"),
        (this.documentURLTransformer = this.createDocumentURLTransformer());
    }
    createDocumentURLTransformer() {
      let e = this;
      return new (class {
        transformFragment(e, t) {
          return Pp + Il(t + (e ? `#${e}` : ""), ":");
        }
        transformURL(t, i) {
          let n = t.match(/^([^#]*)#?(.*)$/);
          if (n) {
            let t = n[1] || i.replace(/\?viv-toc-box$/, ""),
              r = decodeURIComponent(n[2]);
            if (t && e.spine.some((e) => e.src === t))
              return `#${this.transformFragment(r, t)}`;
          }
          return t;
        }
        restoreURL(e) {
          "#" === e.charAt(0) && (e = e.substring(1)),
            0 === e.indexOf(Pp) && (e = e.substring(Pp.length));
          let t = Gp(e, ":").match(/^([^#]*)#?(.*)$/);
          return t ? [t[1], t[2]] : [];
        }
      })();
    }
    getMetadata() {
      return this.metadata;
    }
    getPathFromURL(e) {
      if (e.startsWith("data:")) return e === this.pubURL ? "" : e;
      if (this.pubURL) {
        let t = J("", this.pubURL);
        return e === t || e + "/" === t
          ? ""
          : ("/" != t.charAt(t.length - 1) && (t += "/"),
            e.substr(0, t.length) == t
              ? decodeURIComponent(e.substr(t.length))
              : null);
      }
      return e;
    }
    initWithXMLDoc(e, t) {
      (this.opfXML = e), (this.encXML = t);
      let i = e.doc().child("package"),
        n = i.attribute("unique-identifier")[0];
      if (n) {
        let t = e.getElement(`${e.url}#${n}`);
        t && (this.uid = t.textContent.replace(/[ \n\r\t]/g, ""));
      }
      let r = {};
      (this.items = i
        .child("manifest")
        .child("item")
        .asArray()
        .map((t) => {
          let i = new ll(),
            n = t;
          i.initWithElement(n, e.url);
          let s = n.getAttribute("fallback");
          return (
            s && !Pg[i.mediaType] && (r[i.src] = s),
            !this.toc && i.itemProperties.nav && (this.toc = i),
            !this.cover && i.itemProperties["cover-image"] && (this.cover = i),
            i
          );
        })),
        (this.itemMap = Vl(this.items, kb)),
        (this.itemMapByPath = Vl(this.items, (e) =>
          this.getPathFromURL(e.src)
        ));
      for (let e in r) {
        let t = e;
        for (;;) {
          let i = this.itemMap[r[t]];
          if (!i) break;
          if (Pg[i.mediaType]) {
            this.fallbackMap[e] = i.src;
            break;
          }
          t = i.src;
        }
      }
      this.spine = i
        .child("spine")
        .child("itemref")
        .asArray()
        .map((e, t) => {
          let i = e,
            n = i.getAttribute("idref"),
            r = this.itemMap[n];
          return r && ((r.itemRefElement = i), (r.spineIndex = t)), r;
        });
      let s = i.child("spine").attribute("page-progression-direction")[0];
      s && (this.pageProgression = yl(s));
      let o = t
          ? t
              .doc()
              .child("encryption")
              .child("EncryptedData")
              .predicate(
                vp.withChild(
                  "EncryptionMethod",
                  vp.withAttribute(
                    "Algorithm",
                    "http://www.idpf.org/2008/embedding"
                  )
                )
              )
              .child("CipherData")
              .child("CipherReference")
              .attribute("URI")
          : [],
        a = i.child("bindings").child("mediaType").asArray();
      for (let e = 0; e < a.length; e++) {
        let t = a[e].getAttribute("handler"),
          i = a[e].getAttribute("media-type");
        i && t && this.itemMap[t] && (this.bindings[i] = this.itemMap[t].src);
      }
      if (
        ((this.metadata = Ib(i.child("metadata"), i.attribute("prefix")[0])),
        this.metadata[ve.language] &&
          (this.lang = this.metadata[ve.language][0].v),
        this.metadata[ve.layout] &&
          (this.prePaginated =
            "pre-paginated" === this.metadata[ve.layout][0].v),
        o.length > 0 && this.uid)
      ) {
        let e = Ab(this.uid);
        for (let t = 0; t < o.length; t++)
          this.store.deobfuscators[this.pubURL + o[t]] = e;
      }
      return this.prePaginated && this.assignAutoPages(), T(!0);
    }
    assignAutoPages() {
      let e = 0;
      for (let t of this.spine) {
        let i = this.prePaginated ? 1 : Math.ceil(t.compressedSize / 1024);
        (t.epage = e), (t.epageCount = i), (e += i);
      }
      (this.epageCount = e),
        this.epageCountCallback && this.epageCountCallback(this.epageCount);
    }
    setEPageCountMode(e) {
      this.epageIsRenderedPage = e || this.prePaginated;
    }
    countEPages(e) {
      if (((this.epageCountCallback = e), this.epageIsRenderedPage))
        return (
          this.prePaginated && 0 == this.epageCount && this.assignAutoPages(),
          T(!0)
        );
      let t = 0,
        i = 0,
        n = A("countEPages");
      return (
        n
          .loopWithFrame((e) => {
            if (i === this.spine.length) return void e.breakLoop();
            let n = this.spine[i++];
            (n.epage = t),
              this.store.load(n.src).then((i) => {
                let r = 1800,
                  s = i.lang || this.lang;
                s && s.match(/^(ja|ko|zh)/) && (r /= 3),
                  (n.epageCount = Math.ceil(i.getTotalOffset() / r)),
                  (t += n.epageCount),
                  (this.epageCount = t),
                  this.epageCountCallback &&
                    this.epageCountCallback(this.epageCount),
                  e.continueLoop();
              });
          })
          .thenFinish(n),
        n.result()
      );
    }
    initWithChapters(e, t) {
      (this.itemMap = {}),
        (this.itemMapByPath = {}),
        (this.items = []),
        (this.spine = this.items);
      let i = (this.opfXML = new ji(
        null,
        "",
        new DOMParser().parseFromString("<spine></spine>", "text/xml")
      ));
      return (
        e.forEach((e) => {
          let t = new ll();
          t.initWithParam(e), t.id;
          let n = i.document.createElement("itemref");
          n.setAttribute("idref", t.id),
            i.root.appendChild(n),
            (t.itemRefElement = n),
            (this.itemMap[t.id] = t);
          let r = this.getPathFromURL(e.url);
          null == r && (r = e.url),
            (this.itemMapByPath[r] = t),
            this.items.push(t);
        }),
        t ? this.store.addDocument(e[0].url, t) : T(null)
      );
    }
    initWithWebPubManifest(e, t, i) {
      var n, r, s, o;
      e.readingProgression && (this.pageProgression = e.readingProgression),
        void 0 === this.metadata && (this.metadata = {});
      let a =
        e.name ||
        (null == (n = e.metadata) ? void 0 : n.title) ||
        (null == t ? void 0 : t.title);
      a &&
        (this.metadata[ve.title] = (Array.isArray(a) ? a : [a]).map((e) => {
          var t;
          return { v: null != (t = e.value) ? t : e };
        }));
      let l =
        e.author ||
        e.creator ||
        (null == (r = e.metadata) ? void 0 : r.author) ||
        Array.from(
          null !=
            (s =
              null == t
                ? void 0
                : t.querySelectorAll(
                    "meta[name='author'], meta[name='DC.Creator']"
                  ))
            ? s
            : []
        ).map((e) => e.content);
      l &&
        0 !== l.length &&
        (this.metadata[ve.creator] = (Array.isArray(l) ? l : [l]).map((e) => {
          var t;
          return { v: null != (t = e.name) ? t : e };
        }));
      let h =
        e.inLanguage ||
        (null == (o = e.metadata) ? void 0 : o.language) ||
        (null == t ? void 0 : t.documentElement.lang) ||
        (null == t ? void 0 : t.documentElement.getAttribute("xml:lang"));
      h &&
        (this.metadata[ve.language] = (Array.isArray(h) ? h : [h]).map((e) => ({
          v: e,
        })));
      let u = this.getPathFromURL(this.pubURL);
      if (!e.readingOrder && t && null !== u) {
        e.readingOrder = [encodeURI(u)];
        for (let i of wg(t)) {
          let t = i.getAttribute("href");
          if (
            /^(https?:)?\/\//.test(t) ||
            /\.(jpe?g|png|gif|pdf|svg|mml)([#?]|$)/.test(t)
          )
            continue;
          let n = kt(J(t, this.pubURL)),
            r = this.getPathFromURL(n),
            s = null !== r ? encodeURI(r) : n;
          -1 == e.readingOrder.indexOf(s) && e.readingOrder.push(s);
        }
      }
      let c = [],
        d = 0,
        p = -1;
      [e.readingOrder, e.resources].forEach((t) => {
        t instanceof Array &&
          t.forEach((n) => {
            let r = t === e.readingOrder,
              s = "string" == typeof n ? n : n.url || n.href,
              o =
                "string" == typeof n
                  ? ""
                  : n.encodingFormat || (n.href && n.type) || "";
            if (
              r ||
              "text/html" === o ||
              "application/xhtml+xml" === o ||
              (!o &&
                "stylesheet" !== n.rel &&
                /(^|\/)([^/]+\.(x?html|htm|xht)|[^/.]*)([#?]|$)/.test(s))
            ) {
              let e = i ? i.replace(/\/[^/]+$/, "/") : this.pubURL,
                t = {
                  url: J(An(s), e),
                  index: d++,
                  startPage: null,
                  skipPagesBefore: null,
                };
              "contents" === n.rel && -1 === p && (p = t.index), c.push(t);
            }
          });
      });
      let f = A("initWithWebPubManifest");
      return (
        this.initWithChapters(c).then(() => {
          var t, n;
          -1 !== p && (this.toc = this.items[p]),
            this.toc ||
              (this.toc = i
                ? null == (t = this.items)
                  ? void 0
                  : t[0]
                : this.itemMapByPath[u]);
          let r = null == (n = e.readingOrder) ? void 0 : n.length;
          r && r < this.items.length && this.items.splice(r), f.finish(!0);
        }),
        f.result()
      );
    }
    getCFI(e, t) {
      let i = this.spine[e],
        n = A("getCFI");
      return (
        this.store.load(i.src).then((e) => {
          let r = e.getNodeByOffset(t),
            s = null;
          if (r) {
            let n = e.getNodeOffset(r, 0, !1),
              o = t - n,
              a = new Uo();
            a.prependPathFromNode(r, o, !1, null),
              i.itemRefElement &&
                a.prependPathFromNode(i.itemRefElement, 0, !1, null),
              (s = a.toString());
          }
          n.finish(s);
        }),
        n.result()
      );
    }
    resolveFragment(e) {
      return gn(
        "resolveFragment",
        (t) => {
          if (!e) return void t.finish(null);
          let i,
            n = new Uo();
          if ((n.fromString(e), this.opfXML)) {
            let e = n.navigate(this.opfXML.document);
            if (1 != e.node.nodeType || e.after || !e.ref)
              return void t.finish(null);
            let r = e.node,
              s = r.getAttribute("idref");
            if ("itemref" != r.localName || !s || !this.itemMap[s])
              return void t.finish(null);
            (i = this.itemMap[s]), (n = e.ref);
          } else i = this.spine[0];
          this.store.load(i.src).then((e) => {
            let r = n.navigate(e.document),
              s = e.getNodeOffset(r.node, r.offset, r.after);
            t.finish({
              spineIndex: i.spineIndex,
              offsetInItem: s,
              pageIndex: -1,
            });
          });
        },
        (t, i) => {
          V.warn(i, "Cannot resolve fragment:", e), t.finish(null);
        }
      );
    }
    resolveEPage(e) {
      return gn(
        "resolveEPage",
        (t) => {
          if (e <= 0)
            return void t.finish({
              spineIndex: 0,
              offsetInItem: 0,
              pageIndex: -1,
            });
          if (this.epageIsRenderedPage) {
            let i = this.spine.findIndex(
              (t) =>
                (0 == t.epage && 0 == t.epageCount) ||
                (t.epage <= e && t.epage + t.epageCount > e)
            );
            -1 == i && (i = this.spine.length - 1);
            let n = this.spine[i];
            (!n || 0 == n.epageCount) && (n = this.spine[--i]);
            let r = Math.floor(e - n.epage);
            return void t.finish({
              spineIndex: i,
              offsetInItem: -1,
              pageIndex: r,
            });
          }
          let i = gt(this.spine.length, (t) => {
            let i = this.spine[t];
            return i.epage + i.epageCount > e;
          });
          i == this.spine.length && i--;
          let n = this.spine[i];
          this.store.load(n.src).then((r) => {
            (e -= n.epage) > n.epageCount && (e = n.epageCount);
            let s = 0;
            if (e > 0) {
              let t = r.getTotalOffset();
              (s = Math.round((t * e) / n.epageCount)), s == t && s--;
            }
            t.finish({ spineIndex: i, offsetInItem: s, pageIndex: -1 });
          });
        },
        (t, i) => {
          V.warn(i, "Cannot resolve epage:", e), t.finish(null);
        }
      );
    }
    getEPageFromPosition(e) {
      let t = this.spine[e.spineIndex];
      if (this.epageIsRenderedPage) {
        return T(t.epage + e.pageIndex);
      }
      if (e.offsetInItem <= 0) return T(t.epage);
      let i = A("getEPage");
      return (
        this.store.load(t.src).then((n) => {
          let r = n.getTotalOffset(),
            s = Math.min(r, e.offsetInItem);
          i.finish(t.epage + (s * t.epageCount) / r);
        }),
        i.result()
      );
    }
  },
  al = (e, t) => ({
    page: e,
    position: {
      spineIndex: e.spineIndex,
      pageIndex: t,
      offsetInItem: e.offset,
    },
  }),
  cl = class {
    constructor(e, t, i, n, r) {
      (this.opf = e),
        (this.viewport = t),
        (this.fontMapper = i),
        (this.pageSheetSizeReporter = r),
        p(this, "spineItems", []),
        p(this, "spineItemLoadingContinuations", []),
        p(this, "pref"),
        p(this, "clientLayout"),
        p(this, "counterStore"),
        p(this, "tocAutohide", !1),
        p(this, "tocVisible", !1),
        p(this, "tocView"),
        (this.pref = vr(n)),
        (this.clientLayout = new Ja(t)),
        (this.counterStore = new Fr(e.documentURLTransformer));
    }
    getPage(e) {
      let t = this.spineItems[e.spineIndex];
      return t ? t.pages[e.pageIndex] : null;
    }
    getCurrentPageProgression(e) {
      if (this.opf.pageProgression) return this.opf.pageProgression;
      {
        let t = this.spineItems[e ? e.spineIndex : 0];
        return t ? t.instance.pageProgression : null;
      }
    }
    finishPageContainer(e, t, i) {
      (t.container.style.display = "none"),
        (t.container.style.visibility = "visible"),
        (t.container.style.position = ""),
        (t.container.style.top = ""),
        (t.container.style.left = ""),
        t.container.setAttribute("data-vivliostyle-page-side", t.side);
      let n = e.pages[i];
      if (
        ((t.isFirstPage = 0 == e.item.spineIndex && 0 == i),
        (e.pages[i] = t),
        this.opf.epageIsRenderedPage)
      ) {
        if (0 == i && e.item.spineIndex > 0) {
          let t = this.opf.spine[e.item.spineIndex - 1];
          e.item.epage = t.epage + t.epageCount;
        }
        (e.item.epageCount = e.pages.length),
          (this.opf.epageCount = this.opf.spine.reduce(
            (e, t) => e + t.epageCount,
            0
          )),
          this.opf.epageCountCallback &&
            this.opf.epageCountCallback(this.opf.epageCount);
      }
      if (n)
        e.instance.viewport.contentContainer.replaceChild(
          t.container,
          n.container
        ),
          n.dispatchEvent({
            type: "replaced",
            target: null,
            currentTarget: null,
            preventDefault: null,
            newPage: t,
          });
      else {
        let n = null;
        if (i > 0) n = e.pages[i - 1].container.nextElementSibling;
        else
          for (let t = e.item.spineIndex + 1; t < this.spineItems.length; t++) {
            let e = this.spineItems[t];
            if (e && e.pages[0]) {
              n = e.pages[0].container;
              break;
            }
          }
        e.instance.viewport.contentContainer.insertBefore(t.container, n);
      }
      this.pageSheetSizeReporter(
        {
          width: e.instance.pageSheetWidth,
          height: e.instance.pageSheetHeight,
        },
        e.instance.pageSheetSize,
        e.item.spineIndex,
        e.instance.pageNumberOffset + i
      );
    }
    renderSinglePage(e, t) {
      let i = A("renderSinglePage"),
        n = e.pages[t ? t.page : 0],
        r = this.makePage(e, t);
      return (
        n && (r.pageType = n.pageType),
        e.instance.layoutNextPage(r, t).then((n) => {
          let s = (t = n) ? t.page - 1 : e.layoutPositions.length - 1;
          this.finishPageContainer(e, r, s),
            this.counterStore.finishPage(r.spineIndex, s);
          let o = null;
          if (t) {
            let i = e.layoutPositions[t.page];
            (e.layoutPositions[t.page] = t),
              i &&
                e.pages[t.page] &&
                (t.isSamePosition(i) || (o = this.renderSinglePage(e, t)));
          }
          o || (o = T(!0)),
            o.then(() => {
              let n = this.counterStore.getUnresolvedRefsToPage(r),
                o = 0;
              i.loopWithFrame((e) => {
                if ((o++, o > n.length)) return void e.breakLoop();
                let t = n[o - 1];
                (t.refs = t.refs.filter((e) => !e.isResolved())),
                  0 !== t.refs.length
                    ? this.getPageViewItem(t.spineIndex).then((i) => {
                        if (!i) return void e.continueLoop();
                        let { currentPageType: n, previousPageType: o } =
                            i.instance.styler.cascade,
                          a = i.instance.scopes;
                        (i.instance.scopes = {}),
                          this.counterStore.pushPageCounters(t.pageCounters),
                          this.counterStore.pushReferencesToSolve(t.refs);
                        let l = i.layoutPositions[t.pageIndex];
                        this.renderSinglePage(i, l).then((t) => {
                          (i.instance.styler.cascade.currentPageType = n),
                            (i.instance.styler.cascade.previousPageType = o),
                            (i.instance.scopes = a),
                            this.counterStore.popPageCounters(),
                            this.counterStore.popReferencesToSolve();
                          let l = t.pageAndPosition.position;
                          l.spineIndex === r.spineIndex &&
                            l.pageIndex === s &&
                            (r = t.pageAndPosition.page),
                            e.continueLoop();
                        });
                      })
                    : e.continueLoop();
              }).then(() => {
                r.container.parentElement || (r = e.pages[s]),
                  (r.isLastPage =
                    !t && e.item.spineIndex === this.opf.spine.length - 1),
                  r.isLastPage &&
                    (this.viewport,
                    this.counterStore.finishLastPage(this.viewport)),
                  r.container.setAttribute("data-vivliostyle-page-index", s),
                  r.container.setAttribute(
                    "data-vivliostyle-spine-index",
                    r.spineIndex
                  ),
                  i.finish({
                    pageAndPosition: al(r, s),
                    nextLayoutPosition: t,
                  });
              });
            });
        }),
        i.result()
      );
    }
    normalizeSeekPosition(e, t) {
      let i = e.pageIndex,
        n = -1;
      if (i < 0) {
        n = e.offsetInItem;
        let r = gt(
          t.layoutPositions.length,
          (e) => t.instance.getPosition(t.layoutPositions[e], !0) > n
        );
        i =
          r === t.layoutPositions.length
            ? t.complete
              ? t.layoutPositions.length - 1
              : Number.POSITIVE_INFINITY
            : r - 1;
      } else
        i === Number.POSITIVE_INFINITY &&
          -1 !== e.offsetInItem &&
          (n = e.offsetInItem);
      return { spineIndex: e.spineIndex, pageIndex: i, offsetInItem: n };
    }
    findPage(e, t) {
      let i = A("findPage");
      return (
        this.getPageViewItem(e.spineIndex).then((n) => {
          if (!n) return void i.finish(null);
          let r,
            s = null;
          i.loopWithFrame((o) => {
            let a = this.normalizeSeekPosition(e, n);
            (r = a.pageIndex),
              (s = n.pages[r]),
              s
                ? o.breakLoop()
                : n.complete
                ? ((r = n.layoutPositions.length - 1),
                  (s = n.pages[r]),
                  o.breakLoop())
                : t
                ? this.renderPage(a).then((e) => {
                    e && ((s = e.page), (r = e.position.pageIndex)),
                      o.breakLoop();
                  })
                : i.sleep(100).then(() => {
                    o.continueLoop();
                  });
          }).then(() => {
            i.finish(al(s, r));
          });
        }),
        i.result()
      );
    }
    renderPage(e) {
      let t = A("renderPage");
      return (
        this.getPageViewItem(e.spineIndex).then((i) => {
          if (!i) return void t.finish(null);
          let n = this.normalizeSeekPosition(e, i),
            r = n.pageIndex,
            s = n.offsetInItem,
            o = i.pages[r];
          o
            ? t.finish(al(o, r))
            : t
                .loopWithFrame((e) => {
                  if (r < i.layoutPositions.length) return void e.breakLoop();
                  if (i.complete)
                    return (
                      (r = i.layoutPositions.length - 1), void e.breakLoop()
                    );
                  let t = i.layoutPositions[i.layoutPositions.length - 1];
                  this.renderSinglePage(i, t).then((n) => {
                    let a = n.pageAndPosition.page;
                    if (((t = n.nextLayoutPosition), t)) {
                      if (s >= 0 && i.instance.getPosition(t) > s)
                        return (
                          (o = a),
                          (r = i.layoutPositions.length - 2),
                          void e.breakLoop()
                        );
                      e.continueLoop();
                    } else
                      (o = a),
                        (r = n.pageAndPosition.position.pageIndex),
                        (i.complete = !0),
                        e.breakLoop();
                  });
                })
                .then(() => {
                  o = o || i.pages[r];
                  let e = i.layoutPositions[r];
                  o
                    ? t.finish(al(o, r))
                    : this.renderSinglePage(i, e).then((e) => {
                        e.nextLayoutPosition || (i.complete = !0),
                          t.finish(e.pageAndPosition);
                      });
                });
        }),
        t.result()
      );
    }
    renderAllPages() {
      let e = A("renderAllPages");
      return (
        this.renderPagesUpto(
          {
            spineIndex: this.opf.spine.length - 1,
            pageIndex: Number.POSITIVE_INFINITY,
            offsetInItem: -1,
          },
          !1
        ).then((t) => {
          e.loopWithFrame((t) => {
            this.spineItems.some((e) =>
              null == e
                ? void 0
                : e.pages.some((e) =>
                    null == e ? void 0 : e.fetchers.some((e) => !e.arrived)
                  )
            )
              ? e.sleep(100).then(() => {
                  t.continueLoop();
                })
              : t.breakLoop();
          }).then(() => {
            e.finish(t);
          });
        }),
        e.result()
      );
    }
    renderPagesUpto(e, t) {
      let i = A("renderPagesUpto");
      e || (e = { spineIndex: 0, pageIndex: 0, offsetInItem: 0 });
      let n,
        r = e.spineIndex,
        s = e.pageIndex,
        o = 0;
      return (
        t && (o = r),
        i
          .loopWithFrame((t) => {
            let i = {
              spineIndex: o,
              pageIndex: o === r ? s : Number.POSITIVE_INFINITY,
              offsetInItem: o === r ? e.offsetInItem : -1,
            };
            this.renderPage(i).then((e) => {
              (n = e), ++o > r ? t.breakLoop() : t.continueLoop();
            });
          })
          .then(() => {
            i.finish(n);
          }),
        i.result()
      );
    }
    firstPage(e, t) {
      return this.findPage(
        { spineIndex: 0, pageIndex: 0, offsetInItem: -1 },
        t
      );
    }
    lastPage(e, t) {
      return this.findPage(
        {
          spineIndex: this.opf.spine.length - 1,
          pageIndex: Number.POSITIVE_INFINITY,
          offsetInItem: -1,
        },
        t
      );
    }
    nextPage(e, t) {
      let i = e.spineIndex,
        n = e.pageIndex,
        r = A("nextPage");
      return (
        this.getPageViewItem(i).then((e) => {
          if (e) {
            if (e.complete && n == e.layoutPositions.length - 1) {
              if (i >= this.opf.spine.length - 1) return void r.finish(null);
              i++, (n = 0);
              let t = this.spineItems[i],
                s = t && t.pages[0],
                o = e.pages[e.pages.length - 1];
              s &&
                o &&
                s.side == o.side &&
                (t.pages.forEach((e) => {
                  e.container && e.container.remove();
                }),
                (this.spineItems[i] = null),
                (this.spineItemLoadingContinuations[i] = null));
            } else n++;
            this.findPage(
              { spineIndex: i, pageIndex: n, offsetInItem: -1 },
              t
            ).thenFinish(r);
          } else r.finish(null);
        }),
        r.result()
      );
    }
    previousPage(e, t) {
      let i = e.spineIndex,
        n = e.pageIndex;
      if (0 == n) {
        if (0 == i) return T(null);
        i--, (n = Number.POSITIVE_INFINITY);
      } else n--;
      return this.findPage(
        { spineIndex: i, pageIndex: n, offsetInItem: -1 },
        t
      );
    }
    isRectoPage(e, t) {
      let i = "left" === e.side,
        n = "ltr" === this.getCurrentPageProgression(t);
      return (!i && n) || (i && !n);
    }
    getSpread(e, t) {
      let i = this.getPage(e);
      if (!i) return T({ left: null, right: null });
      let n,
        r = A("getSpread"),
        s = "left" === i.side;
      return (
        (n = this.isRectoPage(i, e)
          ? this.previousPage(e, t)
          : this.nextPage(e, t)),
        n.then((t) => {
          let i = this.getPage(e),
            n = t && t.page;
          n && n.side === i.side && (n = null),
            s
              ? r.finish({ left: i, right: n })
              : r.finish({ left: n, right: i });
        }),
        r.result()
      );
    }
    nextSpread(e, t) {
      let i = this.getPage(e);
      if (!i) return T(null);
      let n = this.isRectoPage(i, e),
        r = this.nextPage(e, t);
      return n
        ? r
        : r.thenAsync((e) => {
            if (e) {
              if (e.page.side === i.side) return r;
              let n = this.nextPage(e.position, t);
              return n.thenAsync((e) => (e ? n : r));
            }
            return T(null);
          });
    }
    previousSpread(e, t) {
      let i = this.getPage(e);
      if (!i) return T(null);
      let n = this.isRectoPage(i, e),
        r = this.previousPage(e, t),
        s = i.container.previousElementSibling;
      return n
        ? r.thenAsync((e) =>
            e
              ? e.page.side === i.side || e.page.container !== s
                ? r
                : this.previousPage(e.position, t)
              : T(null)
          )
        : r;
    }
    navigateToEPage(e, t, i) {
      let n = A("navigateToEPage");
      return (
        this.opf.resolveEPage(e).then((e) => {
          e ? this.findPage(e, i).thenFinish(n) : n.finish(null);
        }),
        n.result()
      );
    }
    navigateToFragment(e, t, i) {
      let n = A("navigateToCFI");
      return (
        this.opf.resolveFragment(e).then((e) => {
          e ? this.findPage(e, i).thenFinish(n) : n.finish(null);
        }),
        n.result()
      );
    }
    navigateTo(e, t, i) {
      V.debug("Navigate to", e);
      let n = this.opf.getPathFromURL(kt(e));
      if (!n) {
        if (this.opf.opfXML && e.match(/^#epubcfi\(/))
          n = this.opf.getPathFromURL(this.opf.opfXML.url);
        else if ("#" === e.charAt(0)) {
          let t = this.opf.documentURLTransformer.restoreURL(e);
          this.opf.opfXML
            ? ((n = this.opf.getPathFromURL(t[0])), null == n && (n = t[0]))
            : (n = t[0]),
            (e = t[0] + (t[1] ? `#${t[1]}` : ""));
        }
        if (null == n) return T(null);
      }
      let r = this.opf.itemMapByPath[n];
      if (!r) {
        if (
          this.opf.opfXML &&
          n == this.opf.getPathFromURL(this.opf.opfXML.url)
        ) {
          let n = e.indexOf("#");
          if (n >= 0) return this.navigateToFragment(e.substr(n + 1), t, i);
        }
        return T(null);
      }
      let s = A("navigateTo");
      return (
        this.getPageViewItem(r.spineIndex).then((t) => {
          if (!t) return void s.finish(null);
          let n = t.xmldoc.getElement(e);
          this.findPage(
            {
              spineIndex: r.spineIndex,
              pageIndex: -1,
              offsetInItem: n ? t.xmldoc.getElementOffset(n) : 0,
            },
            i
          ).thenFinish(s);
        }),
        s.result()
      );
    }
    makePage(e, t) {
      let i = e.instance.viewport,
        n = i.document.createElement("div");
      n.setAttribute("data-vivliostyle-page-container", "true"),
        (n.role = "presentation"),
        rs || (n.style.visibility = "hidden"),
        i.layoutBox.appendChild(n);
      let r = i.document.createElement("div");
      r.setAttribute("data-vivliostyle-bleed-box", "true"),
        (r.role = "presentation"),
        n.appendChild(r);
      let s = new go(n, r);
      if (
        ((s.spineIndex = e.item.spineIndex),
        (s.position = t),
        (s.offset = e.instance.getPosition(t)),
        0 === s.offset &&
          (!e.instance.blankPageAtStart || 0 !== e.pages.length))
      ) {
        let t = this.opf.documentURLTransformer.transformFragment(
          "",
          e.item.src
        );
        r.setAttribute("id", t), s.registerElementWithId(r, t);
      }
      if (i !== this.viewport) {
        let e = Dl(
            this.viewport.width,
            this.viewport.height,
            i.width,
            i.height
          ),
          t = sn(null, new De(e, null), "");
        s.delayedItems.push(new Hn(n, "transform", t));
      }
      return s;
    }
    makeObjectView(e, t, i, n) {
      let r = t.getAttribute("data"),
        s = null;
      if (r) {
        r = J(r, e.url);
        let i = t.getAttribute("media-type");
        if (!i) {
          let e = this.opf.getPathFromURL(r);
          if (e) {
            let t = this.opf.itemMapByPath[e];
            t && (i = t.mediaType);
          }
        }
        if (i) {
          let e = this.opf.bindings[i];
          if (e) {
            (s = this.viewport.document.createElement("iframe")),
              (s.style.border = "none");
            let n = Ll(r),
              o = Ll(i),
              a = new $e();
            a.append(e),
              a.append("?src="),
              a.append(n),
              a.append("&type="),
              a.append(o);
            for (let e = t.firstChild; e; e = e.nextSibling)
              if (1 == e.nodeType) {
                let t = e;
                if (
                  "param" == t.localName &&
                  "http://www.w3.org/1999/xhtml" == t.namespaceURI
                ) {
                  let e = t.getAttribute("name"),
                    i = t.getAttribute("value");
                  e &&
                    i &&
                    (a.append("&"),
                    a.append(encodeURIComponent(e)),
                    a.append("="),
                    a.append(encodeURIComponent(i)));
                }
              }
            s.setAttribute("src", a.toString());
            let l = t.getAttribute("width");
            l && s.setAttribute("width", l);
            let h = t.getAttribute("height");
            h && s.setAttribute("height", h);
          }
        }
      }
      return (
        s ||
          ((s = this.viewport.document.createElement("object")),
          r && s.setAttribute("data", r),
          s.setAttribute("data-adapt-process-children", "true")),
        T(s)
      );
    }
    makeMathJaxView(e, t, i, n) {
      let r = Vb();
      if (r) {
        let n = i.ownerDocument,
          s = n.createElement("span");
        i.appendChild(s);
        let o = n.importNode(t, !0);
        this.resolveURLsInMathML(o, e), s.appendChild(o);
        let a = r.queue;
        a.Push(["Typeset", r, s]);
        let l = A("makeMathJaxView"),
          h = l.suspend();
        return (
          a.Push(() => {
            h.schedule(s);
          }),
          l.result()
        );
      }
      return T(null);
    }
    resolveURLsInMathML(e, t) {
      if (null != e) {
        if (1 === e.nodeType && "mglyph" === e.tagName) {
          let i = Array.from(e.attributes);
          for (let n of i) {
            if ("src" !== n.name) continue;
            let i = J(n.nodeValue, t.url);
            n.namespaceURI
              ? e.setAttributeNS(n.namespaceURI, n.name, i)
              : e.setAttribute(n.name, i);
          }
        }
        e.firstChild && this.resolveURLsInMathML(e.firstChild, t),
          e.nextSibling && this.resolveURLsInMathML(e.nextSibling, t);
      }
    }
    makeCustomRenderer(e) {
      return (t, i, n) =>
        "object" == t.localName &&
        "http://www.w3.org/1999/xhtml" == t.namespaceURI
          ? this.makeObjectView(e, t, i, n)
          : "http://www.w3.org/1998/Math/MathML" == t.namespaceURI ||
            (t.dataset && "true" == t.dataset.mathTypeset)
          ? this.makeMathJaxView(e, t, i, n)
          : T(null);
    }
    getPageViewItem(e) {
      if (-1 === e || e >= this.opf.spine.length) return T(null);
      let t = this.spineItems[e];
      if (t) return T(t);
      let i = A("getPageViewItem"),
        n = this.spineItemLoadingContinuations[e];
      if (n) {
        let e = i.suspend();
        return n.push(e), i.result();
      }
      n = this.spineItemLoadingContinuations[e] = [];
      let r = this.opf.spine[e],
        s = this.opf.store;
      return (
        s.load(r.src).then((o) => {
          var a;
          let l = r.itemRefElement.getAttribute("properties");
          l && o.root.setAttribute("data-vivliostyle-epub-spine-properties", l),
            (r.title = o.document.title);
          let h = s.getStyleForDoc(o),
            u = this.makeCustomRenderer(o),
            c = this.viewport,
            d = h.sizeViewport(c.width, c.height, c.fontSize, this.pref);
          (d.width != c.width ||
            d.height != c.height ||
            d.fontSize != c.fontSize) &&
            (c = new wn(
              c.window,
              d.fontSize,
              c.pixelRatio,
              c.root,
              d.width,
              d.height
            ));
          let p,
            f,
            g =
              null == (a = this.spineItems[0])
                ? void 0
                : a.instance.isVersoFirstPage,
            m = this.spineItems[e - 1];
          if (null !== r.startPage) (p = r.startPage - 1), (f = p);
          else {
            if (!(e > 0) || (m && m.complete)) {
              p = m ? m.instance.pageNumberOffset + m.pages.length : 0;
              let e = this.counterStore.currentPageCounters.page;
              f = e && e.length ? e[e.length - 1] : p;
            } else
              (p = r.epage || e),
                !this.opf.prePaginated && p % 2 == (g ? 1 : 0) && p++,
                (f = p);
            null !== r.skipPagesBefore &&
              ((p += r.skipPagesBefore), (f += r.skipPagesBefore));
          }
          this.counterStore.forceSetPageCounter(f);
          let w = new ss(
            h,
            o,
            this.opf.lang,
            c,
            this.clientLayout,
            this.fontMapper,
            u,
            this.opf.fallbackMap,
            p,
            this.opf.documentURLTransformer,
            this.counterStore,
            this.opf.pageProgression,
            g
          );
          w.pref = this.pref;
          let b = this.opf.metadata && this.opf.metadata[ve.title];
          (w.pubTitle = (b && b[0] && b[0].v) || ""),
            (w.docTitle = r.title || ""),
            w.init().then(() => {
              !this.opf.pageProgression &&
                w.pageProgression &&
                (this.opf.pageProgression = w.pageProgression),
                (t = {
                  item: r,
                  xmldoc: o,
                  instance: w,
                  layoutPositions: [null],
                  pages: [],
                  complete: !1,
                }),
                (this.spineItems[e] = t),
                i.finish(t),
                n.forEach((e) => {
                  e.schedule(t);
                });
            });
        }),
        i.result()
      );
    }
    removeRenderedPages() {
      let e = this.spineItems;
      for (let t of e) t && t.pages.splice(0);
      this.viewport.clear();
    }
    hasAutoSizedPages() {
      let e = this.spineItems;
      for (let t of e)
        if (t) {
          let e = t.pages;
          for (let t of e)
            if (t.isAutoPageWidth && t.isAutoPageHeight) return !0;
        }
      return !1;
    }
    hasPages() {
      return this.spineItems.some((e) => e && e.pages.length > 0);
    }
    showTOC(e) {
      let t = this.opf,
        i = t.toc;
      if (((this.tocAutohide = e), !i)) return T(null);
      if (((this.tocVisible = !0), this.tocView && this.tocView.page))
        return (
          (this.tocView.page.container.style.visibility = "visible"),
          this.tocView.page.container.setAttribute("aria-hidden", "false"),
          T(this.tocView.page)
        );
      let n = A("showTOC");
      this.tocView ||
        (this.tocView = new il(
          t.store,
          i.src,
          t.lang,
          this.clientLayout,
          this.fontMapper,
          this.pref,
          this,
          t.fallbackMap,
          t.documentURLTransformer,
          this.counterStore
        ));
      let r = this.viewport,
        s = Math.min(344, Math.round(0.67 * r.width) - 16),
        o = r.height - 6,
        a = r.document.createElement("div");
      return (
        r.root.appendChild(a),
        rs || (a.style.visibility = "hidden"),
        (a.style.width = `${s + 16}px`),
        (a.style.maxHeight = `${o}px`),
        a.setAttribute("data-vivliostyle-toc-box", "true"),
        a.setAttribute("role", "navigation"),
        this.tocView.showTOC(a, r, s, o, this.viewport.fontSize).then((e) => {
          (a.style.visibility = "visible"),
            a.setAttribute("aria-hidden", "false"),
            n.finish(e);
        }),
        n.result()
      );
    }
    hideTOC() {
      (this.tocVisible = !1), this.tocView && this.tocView.hideTOC();
    }
    isTOCVisible() {
      return this.tocVisible && !!this.tocView && this.tocView.isTOCVisible();
    }
  },
  kg = "data-vivliostyle-viewer-status",
  Bb = "data-vivliostyle-spread-view",
  kp = ((e) => (
    (e.SINGLE_PAGE = "singlePage"),
    (e.SPREAD = "spread"),
    (e.AUTO_SPREAD = "autoSpread"),
    e
  ))(kp || {}),
  ul = class {
    constructor(e, t, i, n) {
      (this.window = e),
        (this.viewportElement = t),
        (this.instanceId = i),
        (this.callbackFn = n),
        p(this, "fontMapper"),
        p(this, "kick"),
        p(this, "sendCommand"),
        p(this, "resizeListener"),
        p(this, "hyperlinkListener"),
        p(this, "pageRuleStyleElement"),
        p(this, "pageSheetSizeAlreadySet", !1),
        p(this, "renderTask", null),
        p(this, "actions"),
        p(this, "readyState"),
        p(this, "packageURL"),
        p(this, "opf"),
        p(this, "touchActive"),
        p(this, "touchX"),
        p(this, "touchY"),
        p(this, "needResize"),
        p(this, "resized"),
        p(this, "needRefresh"),
        p(this, "viewportSize"),
        p(this, "currentPage"),
        p(this, "currentSpread"),
        p(this, "pagePosition"),
        p(this, "fontSize"),
        p(this, "zoom"),
        p(this, "fitToScreen"),
        p(this, "pageViewMode"),
        p(this, "waitForLoading"),
        p(this, "renderAllPages"),
        p(this, "pref"),
        p(this, "pageSizes"),
        p(this, "pixelRatio"),
        p(this, "pixelRatioLimit"),
        p(this, "viewport"),
        p(this, "opfView");
      let r = t.ownerDocument,
        s = (e, t) => {
          let i = r.getElementById(e);
          return (
            i ||
              ((i = r.createElement("style")),
              (i.id = e),
              t && (i.textContent = t),
              r.head.appendChild(i)),
            i
          );
        };
      s("vivliostyle-viewport-screen-css", mc),
        s("vivliostyle-viewport-css", Cc),
        s("vivliostyle-polyfill-css", Nc),
        t.setAttribute("data-vivliostyle-viewer-viewport", !0),
        rs && t.setAttribute("data-vivliostyle-debug", !0),
        t.setAttribute(kg, "loading"),
        (this.fontMapper = new _a(r.head, t)),
        this.init(),
        (this.kick = () => {}),
        (this.sendCommand = () => {}),
        (this.resizeListener = () => {
          (this.needResize = !0), (this.resized = !0), this.kick();
        }),
        (this.pageReplacedListener = this.pageReplacedListener.bind(this)),
        (this.hyperlinkListener = (e) => {}),
        (this.pageRuleStyleElement = s("vivliostyle-page-rules")),
        (this.actions = {
          loadPublication: this.loadPublication,
          loadXML: this.loadXML,
          configure: this.configure,
          moveTo: this.moveTo,
          toc: this.showTOC,
        }),
        this.addLogListeners();
    }
    init() {
      (this.readyState = "loading"),
        (this.packageURL = []),
        (this.opf = null),
        (this.touchActive = !1),
        (this.touchX = 0),
        (this.touchY = 0),
        (this.needResize = !1),
        (this.resized = !1),
        (this.needRefresh = !1),
        (this.viewportSize = null),
        (this.currentPage = null),
        (this.currentSpread = null),
        (this.pagePosition = null),
        (this.fontSize = 16),
        (this.zoom = 1),
        (this.fitToScreen = !1),
        (this.pageViewMode = "singlePage"),
        (this.waitForLoading = !1),
        (this.renderAllPages = !0),
        (this.pref = Ol()),
        (this.pageSizes = []),
        (this.pixelRatioLimit = /Chrome/.test(navigator.userAgent) ? 16 : 0),
        (this.pixelRatio = Math.min(8, this.pixelRatioLimit));
    }
    addLogListeners() {
      let e = Nl;
      V.addListener(e.DEBUG, (e) => {
        this.callback({ t: "debug", content: e });
      }),
        V.addListener(e.INFO, (e) => {
          this.callback({ t: "info", content: e });
        }),
        V.addListener(e.WARN, (e) => {
          this.callback({ t: "warn", content: e });
        }),
        V.addListener(e.ERROR, (e) => {
          this.callback({ t: "error", content: e });
        });
    }
    callback(e) {
      (e.i = this.instanceId), this.callbackFn(e);
    }
    setReadyState(e) {
      this.readyState !== e &&
        ((this.readyState = e),
        this.viewportElement.setAttribute(kg, e),
        this.callback({ t: "readystatechange" }));
    }
    loadPublication(e) {
      Ye.registerStartTiming("beforeRender"), this.setReadyState("loading");
      let t = e.url,
        i = e.fragment,
        n = e.authorStyleSheet,
        r = e.userStyleSheet;
      this.viewport = null;
      let s = A("loadPublication");
      return (
        this.configure(e).then(() => {
          let e = new Ki();
          e.init(n, r).then(() => {
            let n = J(An(t), this.window.location.href);
            (this.packageURL = [n]),
              e.loadPubDoc(n).then((e) => {
                e
                  ? ((this.opf = e),
                    this.render(i).then(() => {
                      s.finish(!0);
                    }))
                  : s.finish(!1);
              });
          });
        }),
        s.result()
      );
    }
    loadXML(e) {
      Ye.registerStartTiming("beforeRender"), this.setReadyState("loading");
      let t = e.url,
        i = e.document,
        n = e.fragment,
        r = e.authorStyleSheet,
        s = e.userStyleSheet;
      this.viewport = null;
      let o = A("loadXML");
      return (
        this.configure(e).then(() => {
          let e = new Ki();
          e.init(r, s).then(() => {
            let r = t.map((e, t) => ({
              url: J(An(e.url), this.window.location.href),
              index: t,
              startPage: e.startPage,
              skipPagesBefore: e.skipPagesBefore,
            }));
            (this.packageURL = r.map((e) => e.url)),
              (this.opf = new Gs(e, "")),
              this.opf.initWithChapters(r, i).then(() => {
                this.render(n).then(() => {
                  o.finish(!0);
                });
              });
          });
        }),
        o.result()
      );
    }
    render(e) {
      let t;
      return (
        this.cancelRenderingTask(),
        (t = e
          ? this.opf
              .resolveFragment(e)
              .thenAsync((e) => ((this.pagePosition = e), T(!0)))
          : T(!0)),
        t.thenAsync(() => (Ye.registerEndTiming("beforeRender"), this.resize()))
      );
    }
    resolveLength(e) {
      let t,
        i = parseFloat(e);
      if ("string" == typeof e && (t = e.match(/[a-z]+$/))) {
        let e = t[0];
        if ("em" === e || "rem" === e) return i * this.fontSize;
        if ("ex" === e) return (i * Y.ex * this.fontSize) / Y.em;
        let n = Y[e];
        if (n) return i * n;
      }
      return i;
    }
    configure(e) {
      if (
        ("boolean" == typeof e.autoresize &&
          (e.autoresize
            ? ((this.viewportSize = null),
              this.window.addEventListener("resize", this.resizeListener, !1),
              (this.needResize = !0))
            : this.window.removeEventListener(
                "resize",
                this.resizeListener,
                !1
              )),
        "number" == typeof e.fontSize)
      ) {
        let t = e.fontSize;
        t >= 5 &&
          t <= 72 &&
          this.fontSize != t &&
          ((this.fontSize = t), (this.needResize = !0));
      }
      if ("object" == typeof e.viewport && e.viewport) {
        let t = e.viewport,
          i = {
            marginLeft: this.resolveLength(t["margin-left"]) || 0,
            marginRight: this.resolveLength(t["margin-right"]) || 0,
            marginTop: this.resolveLength(t["margin-top"]) || 0,
            marginBottom: this.resolveLength(t["margin-bottom"]) || 0,
            width: this.resolveLength(t.width) || 0,
            height: this.resolveLength(t.height) || 0,
          };
        (i.width >= 200 || i.height >= 200) &&
          (this.window.removeEventListener("resize", this.resizeListener, !1),
          (this.viewportSize = i),
          (this.needResize = !0));
      }
      if (
        ("boolean" == typeof e.hyphenate &&
          ((this.pref.hyphenate = e.hyphenate), (this.needResize = !0)),
        "boolean" == typeof e.horizontal &&
          ((this.pref.horizontal = e.horizontal), (this.needResize = !0)),
        "boolean" == typeof e.nightMode &&
          ((this.pref.nightMode = e.nightMode), (this.needResize = !0)),
        "number" == typeof e.lineHeight &&
          ((this.pref.lineHeight = e.lineHeight), (this.needResize = !0)),
        "number" == typeof e.columnWidth &&
          ((this.pref.columnWidth = e.columnWidth), (this.needResize = !0)),
        "string" == typeof e.fontFamily &&
          ((this.pref.fontFamily = e.fontFamily), (this.needResize = !0)),
        "boolean" == typeof e.load && (this.waitForLoading = e.load),
        "boolean" == typeof e.renderAllPages &&
          (this.renderAllPages = e.renderAllPages),
        "string" == typeof e.userAgentRootURL &&
          (wl(e.userAgentRootURL.replace(/resources\/?$/, "")),
          Pl(e.userAgentRootURL)),
        "string" == typeof e.rootURL && (wl(e.rootURL), Pl(`${dn}resources/`)),
        "string" == typeof e.pageViewMode &&
          e.pageViewMode !== this.pageViewMode &&
          ((this.pageViewMode = e.pageViewMode), (this.needResize = !0)),
        "number" == typeof e.pageBorder &&
          e.pageBorder !== this.pref.pageBorder &&
          ((this.viewport = null),
          (this.pref.pageBorder = e.pageBorder),
          (this.needResize = !0)),
        "number" == typeof e.zoom &&
          e.zoom !== this.zoom &&
          ((this.zoom = e.zoom), (this.needRefresh = !0)),
        "boolean" == typeof e.fitToScreen &&
          e.fitToScreen !== this.fitToScreen &&
          ((this.fitToScreen = e.fitToScreen), (this.needRefresh = !0)),
        "object" == typeof e.defaultPaperSize &&
          "number" == typeof e.defaultPaperSize.width &&
          "number" == typeof e.defaultPaperSize.height &&
          ((this.viewport = null),
          (this.pref.defaultPaperSize = e.defaultPaperSize),
          (this.needResize = !0)),
        "boolean" == typeof e.allowScripts &&
          e.allowScripts !== Xt &&
          (Cg(e.allowScripts), (this.needResize = !0)),
        "number" == typeof e.pixelRatio)
      ) {
        let t = Math.min(e.pixelRatio, this.pixelRatioLimit);
        t !== this.pixelRatio &&
          ((this.pixelRatio = t), (this.needResize = !0));
      }
      return this.configurePlugins(e), T(!0);
    }
    configurePlugins(e) {
      Ge("CONFIGURATION").forEach((t) => {
        let i = t(e);
        (this.needResize = i.needResize || this.needResize),
          (this.needRefresh = i.needRefresh || this.needRefresh);
      });
    }
    pageReplacedListener(e) {
      let t = this.currentPage,
        i = this.currentSpread,
        n = e.target;
      i
        ? (i.left === n || i.right === n) && this.showCurrent(e.newPage)
        : t === e.target && this.showCurrent(e.newPage);
    }
    forCurrentPages(e) {
      let t = [];
      this.currentPage && t.push(this.currentPage),
        this.currentSpread &&
          (t.push(this.currentSpread.left), t.push(this.currentSpread.right)),
        t.forEach((t) => {
          t && e(t);
        });
    }
    removePageListeners() {
      this.forCurrentPages((e) => {
        e.removeEventListener("hyperlink", this.hyperlinkListener, !1),
          e.removeEventListener("replaced", this.pageReplacedListener, !1);
      });
    }
    hidePages() {
      this.removePageListeners(),
        this.forCurrentPages((e) => {
          w(e.container, "display", "none");
        }),
        (this.currentPage = null),
        (this.currentSpread = null);
    }
    showSinglePage(e) {
      e.addEventListener("hyperlink", this.hyperlinkListener, !1),
        e.addEventListener("replaced", this.pageReplacedListener, !1),
        w(e.container, "visibility", "visible"),
        w(e.container, "display", "block");
    }
    showPage(e) {
      this.hidePages(),
        (this.currentPage = e),
        (e.container.style.marginLeft = ""),
        (e.container.style.marginRight = ""),
        this.showSinglePage(e);
    }
    showSpread(e) {
      if ((this.hidePages(), (this.currentSpread = e), e.left && e.right)) {
        let t = parseFloat(e.left.container.style.width),
          i = parseFloat(e.right.container.style.width);
        t &&
          i &&
          t !== i &&
          (t < i
            ? (e.left.container.style.marginLeft = i - t + "px")
            : (e.right.container.style.marginRight = t - i + "px"));
      }
      e.left &&
        (this.showSinglePage(e.left),
        e.right
          ? e.left.container.removeAttribute("data-vivliostyle-unpaired-page")
          : e.left.container.setAttribute(
              "data-vivliostyle-unpaired-page",
              !0
            )),
        e.right &&
          (this.showSinglePage(e.right),
          e.left
            ? e.right.container.removeAttribute(
                "data-vivliostyle-unpaired-page"
              )
            : e.right.container.setAttribute(
                "data-vivliostyle-unpaired-page",
                !0
              ));
    }
    reportPosition() {
      let e = A("reportPosition");
      return (
        this.pagePosition,
        this.opf
          .getCFI(this.pagePosition.spineIndex, this.pagePosition.offsetInItem)
          .then((t) => {
            let i = this.currentPage;
            (this.waitForLoading && i.fetchers.length > 0
              ? Bn(i.fetchers)
              : T(!0)
            ).then(() => {
              this.sendLocationNotification(i, t).thenFinish(e);
            });
          }),
        e.result()
      );
    }
    createViewport() {
      let e = this.viewportElement;
      if (this.viewportSize) {
        let t = this.viewportSize;
        return (
          (e.style.marginLeft = `${t.marginLeft}px`),
          (e.style.marginRight = `${t.marginRight}px`),
          (e.style.marginTop = `${t.marginTop}px`),
          (e.style.marginBottom = `${t.marginBottom}px`),
          new wn(
            this.window,
            this.fontSize,
            this.pixelRatio,
            e,
            t.width,
            t.height
          )
        );
      }
      return new wn(this.window, this.fontSize, this.pixelRatio, e);
    }
    resolveSpreadView(e, t) {
      switch (this.pageViewMode) {
        case "singlePage":
          return !1;
        case "spread":
          return !0;
        default:
          return (
            (e.width - this.pref.pageBorder) / e.height >=
              (t ? (2 * t.width) / t.height : 1.45) &&
            (!!t || e.width > 800)
          );
      }
    }
    updateSpreadView(e) {
      (this.pref.spreadView = e),
        this.viewportElement.setAttribute(Bb, e.toString());
    }
    sizeIsGood() {
      var e;
      let t = this.createViewport(),
        i =
          (null == (e = this.opfView) ? void 0 : e.hasPages()) &&
          !this.opfView.hasAutoSizedPages(),
        n = this.resolveSpreadView(
          t,
          this.resized && i ? this.pageSizes[0] : null
        );
      this.resized = !1;
      let r = this.pref.spreadView !== n;
      return (
        this.updateSpreadView(n),
        !(
          (this.pixelRatio &&
            this.opfView &&
            this.pixelRatio / this.window.devicePixelRatio !==
              this.opfView.clientLayout.scaleRatio) ||
          this.viewportSize ||
          !this.viewport ||
          this.viewport.fontSize != this.fontSize
        ) &&
          (!!(
            (!r &&
              t.width == this.viewport.width &&
              t.height == this.viewport.height) ||
            (!r &&
              t.width == this.viewport.width &&
              t.height != this.viewport.height &&
              /Android|iPhone|iPad|iPod/.test(navigator.userAgent))
          ) ||
            (!!i &&
              ((this.viewport.width = t.width),
              (this.viewport.height = t.height),
              (this.needRefresh = !0),
              !0)))
      );
    }
    setPageSize(e, t, i, n) {
      (this.pageSizes[n] = e),
        this.setPageSizePageRules(t, i, n),
        0 === n &&
          "autoSpread" === this.pageViewMode &&
          !this.opfView.hasAutoSizedPages() &&
          this.updateSpreadView(this.resolveSpreadView(this.viewport, e));
    }
    setPageSizePageRules(e, t, i) {
      var n, r;
      if (
        this.pageRuleStyleElement &&
        (!this.pageSheetSizeAlreadySet ||
          this.pageSizes[i].width !==
            (null == (n = this.pageSizes[i - 1]) ? void 0 : n.width) ||
          this.pageSizes[i].height !==
            (null == (r = this.pageSizes[i - 1]) ? void 0 : r.height))
      ) {
        let e = function (e) {
          let t = 0.75 * e;
          return Math.ceil(t);
        };
        let t = Math.max(...this.pageSizes.map((e) => e.width)),
          i = Math.max(...this.pageSizes.map((e) => e.height)),
          n = e(t),
          r = e(i),
          s = `@page {size: ${n}pt ${r}pt; margin: 0 ${-(
            n * ((this.pixelRatio || 1) - 1) +
            2
          )}pt ${-(r * ((this.pixelRatio || 1) - 1) + 2)}pt 0;}`;
        (this.pageRuleStyleElement.textContent = s),
          (this.pageSheetSizeAlreadySet = !0);
      }
    }
    removePageSizePageRules() {
      this.pageRuleStyleElement &&
        ((this.pageRuleStyleElement.textContent = ""),
        (this.pageSheetSizeAlreadySet = !1));
    }
    reset() {
      let e = !1,
        t = !1;
      this.opfView &&
        ((e = this.opfView.tocVisible),
        (t = this.opfView.tocAutohide),
        this.opfView.removeRenderedPages()),
        (this.pageSizes = []),
        this.removePageSizePageRules(),
        (this.viewport = this.createViewport()),
        this.viewport.resetZoom(),
        (this.opfView = new cl(
          this.opf,
          this.viewport,
          this.fontMapper,
          this.pref,
          this.setPageSize.bind(this)
        )),
        e && this.sendCommand({ a: "toc", v: "show", autohide: t });
    }
    showCurrent(e, t) {
      (this.needRefresh = !1), this.removePageListeners();
      let i = this.resolveSpreadView(this.viewport, e.dimensions);
      return (
        i !== this.pref.spreadView && this.updateSpreadView(i),
        i
          ? this.opfView
              .getSpread(this.pagePosition, t)
              .thenAsync((t) =>
                t.left || t.right
                  ? !t.left ||
                    !t.right ||
                    (this.resolveSpreadView(this.viewport, t.left.dimensions) &&
                      this.resolveSpreadView(this.viewport, t.right.dimensions))
                    ? (this.showSpread(t),
                      this.setSpreadZoom(t),
                      (this.currentPage = "left" === e.side ? t.left : t.right),
                      T(null))
                    : (this.updateSpreadView(!1),
                      this.showPage(e),
                      this.setPageZoom(e),
                      (this.currentPage = e),
                      T(null))
                  : T(null)
              )
          : (this.showPage(e),
            this.setPageZoom(e),
            (this.currentPage = e),
            T(null))
      );
    }
    setPageZoom(e) {
      let t = this.getAdjustedZoomFactor(e.dimensions);
      this.viewport.zoom(e.dimensions.width, e.dimensions.height, t);
    }
    setSpreadZoom(e) {
      let t = this.getSpreadDimensions(e);
      this.viewport.zoom(t.width, t.height, this.getAdjustedZoomFactor(t));
    }
    getAdjustedZoomFactor(e) {
      return this.fitToScreen
        ? this.calculateZoomFactorToFitInsideViewPort(e)
        : this.zoom;
    }
    getSpreadDimensions(e) {
      let t = 0,
        i = 0;
      return (
        e.left &&
          ((t += e.left.dimensions.width), (i = e.left.dimensions.height)),
        e.right &&
          ((t += e.right.dimensions.width),
          (i = Math.max(i, e.right.dimensions.height))),
        e.left &&
          e.right &&
          ((t += 2 * this.pref.pageBorder),
          (t += Math.abs(e.left.dimensions.width - e.right.dimensions.width))),
        { width: t, height: i }
      );
    }
    queryZoomFactor(e) {
      if (!this.currentPage) throw new Error("no page exists.");
      if ("fit inside viewport" === e) {
        let e;
        return (
          this.pref.spreadView
            ? (this.currentSpread,
              (e = this.getSpreadDimensions(this.currentSpread)))
            : (e = this.currentPage.dimensions),
          this.calculateZoomFactorToFitInsideViewPort(e)
        );
      }
      throw new Error(`unknown zoom type: ${e}`);
    }
    calculateZoomFactorToFitInsideViewPort(e) {
      if (!this.viewport) return this.zoom;
      let t = this.viewport.width / e.width,
        i = this.viewport.height / e.height;
      return Math.min(t, i);
    }
    cancelRenderingTask() {
      this.renderTask && this.renderTask.interrupt(new dl()),
        (this.renderTask = null);
    }
    resize() {
      if (((this.needResize = !1), (this.needRefresh = !1), this.sizeIsGood()))
        return T(!0);
      this.setReadyState("loading"), this.cancelRenderingTask();
      let e = Jo()
        .getScheduler()
        .run(() =>
          gn(
            "resize",
            (t) => {
              this.opf
                ? ((this.renderTask = e),
                  Ye.registerStartTiming("render (resize)"),
                  this.reset(),
                  this.pagePosition &&
                    ((0 == this.pagePosition.pageIndex &&
                      0 == this.pagePosition.offsetInItem) ||
                      (this.pagePosition.pageIndex = -1)),
                  this.opf.setEPageCountMode(this.renderAllPages),
                  this.opfView
                    .renderPagesUpto(this.pagePosition, !this.renderAllPages)
                    .then((i) => {
                      i
                        ? ((this.pagePosition = i.position),
                          this.showCurrent(i.page, !0).then(() => {
                            this.setReadyState("interactive"),
                              this.opf
                                .countEPages((e) => {
                                  let t = {
                                    t: "nav",
                                    epageCount: e,
                                    first: this.currentPage.isFirstPage,
                                    last: this.currentPage.isLastPage,
                                    metadata: this.opf.metadata,
                                    docTitle:
                                      this.opf.spine[
                                        this.pagePosition.spineIndex
                                      ].title,
                                  };
                                  (this.currentPage.isFirstPage ||
                                    (0 == this.pagePosition.pageIndex &&
                                      this.opf.spine[
                                        this.pagePosition.spineIndex
                                      ].epage)) &&
                                    (t.epage =
                                      this.opf.spine[
                                        this.pagePosition.spineIndex
                                      ].epage),
                                    this.callback(t);
                                })
                                .then(() => {
                                  this.reportPosition().then((i) => {
                                    (this.renderAllPages
                                      ? this.opfView.renderAllPages()
                                      : T(null)
                                    ).then(() => {
                                      this.renderTask === e &&
                                        (this.renderTask = null),
                                        Ye.registerEndTiming("render (resize)"),
                                        Xt && xp(this.window)
                                          ? yg(this.window).then(() => {
                                              this.renderAllPages &&
                                                this.setReadyState("complete"),
                                                this.callback({ t: "loaded" }),
                                                t.finish(i);
                                            })
                                          : (this.renderAllPages &&
                                              this.setReadyState("complete"),
                                            this.callback({ t: "loaded" }),
                                            t.finish(i));
                                    });
                                  });
                                });
                          }))
                        : t.finish(!1);
                    }))
                : t.finish(!1);
            },
            (e, t) => {
              if (!(t instanceof dl)) throw t;
              Ye.registerEndTiming("render (resize)"), V.debug(t.message);
            }
          )
        );
      return T(!0);
    }
    sendLocationNotification(e, t) {
      let i = A("sendLocationNotification"),
        n = {
          t: "nav",
          first: e.isFirstPage,
          last: e.isLastPage,
          metadata: this.opf.metadata,
          docTitle: this.opf.spine[e.spineIndex].title,
        };
      return (
        this.opf.getEPageFromPosition(this.pagePosition).then((e) => {
          (n.epage = e),
            (n.epageCount = this.opf.epageCount),
            t && (n.cfi = t),
            this.callback(n),
            i.finish(!0);
        }),
        i.result()
      );
    }
    getCurrentPageProgression() {
      return this.opfView
        ? this.opfView.getCurrentPageProgression(this.pagePosition)
        : null;
    }
    moveTo(e) {
      var t;
      let i;
      if (
        ("complete" !== this.readyState &&
          "next" !== e.where &&
          this.setReadyState("loading"),
        "string" == typeof e.where)
      ) {
        let t;
        switch (e.where) {
          case "next":
            t = this.pref.spreadView
              ? this.opfView.nextSpread
              : this.opfView.nextPage;
            break;
          case "previous":
            t = this.pref.spreadView
              ? this.opfView.previousSpread
              : this.opfView.previousPage;
            break;
          case "last":
            t = this.opfView.lastPage;
            break;
          case "first":
            t = this.opfView.firstPage;
            break;
          default:
            return T(!0);
        }
        t &&
          (i = () =>
            t.call(this.opfView, this.pagePosition, !this.renderAllPages));
      } else if ("number" == typeof e.epage) {
        let t = e.epage;
        i = () =>
          this.opfView.navigateToEPage(
            t,
            this.pagePosition,
            !this.renderAllPages
          );
      } else if ("string" == typeof e.url) {
        let t = e.url;
        i = () =>
          this.opfView.navigateTo(t, this.pagePosition, !this.renderAllPages);
      } else {
        if (
          "number" != typeof (null == (t = e.position) ? void 0 : t.spineIndex)
        )
          return T(!0);
        {
          let t = e.position;
          i = () => this.opfView.findPage(t, !this.renderAllPages);
        }
      }
      if (!this.opfView) return T(!0);
      let n = A("moveTo");
      return (
        i.call(this.opfView).then((e) => {
          let t;
          if (e) {
            this.pagePosition = e.position;
            let i = A("moveTo.showCurrent");
            (t = i.result()),
              this.showCurrent(e.page, !this.renderAllPages).then(() => {
                this.reportPosition().thenFinish(i);
              });
          } else t = T(!0);
          t.then((e) => {
            "loading" === this.readyState && this.setReadyState("interactive"),
              n.finish(e);
          });
        }),
        n.result()
      );
    }
    showTOC(e) {
      let t = !!e.autohide,
        i = e.v,
        n = this.opfView.isTOCVisible(),
        r = t != this.opfView.tocAutohide && "hide" != i;
      if (n) {
        if ("show" == i && !r) return T(!0);
      } else if ("hide" == i) return T(!0);
      if (n && "show" != i) return this.opfView.hideTOC(), T(!0);
      {
        let e = A("showTOC");
        return (
          this.opfView.showTOC(t).then((i) => {
            if (i) {
              if ((r && (i.listeners = {}), t)) {
                let e = () => {
                  this.opfView.hideTOC();
                };
                i.addEventListener("hyperlink", e, !1);
              }
              i.addEventListener("hyperlink", this.hyperlinkListener, !1);
            }
            e.finish(!0);
          }),
          e.result()
        );
      }
    }
    runCommand(e) {
      let t = e.a || "";
      return gn(
        "runCommand",
        (i) => {
          let n = this.actions[t];
          n
            ? n.call(this, e).then(() => {
                this.callback({ t: "done", a: t }), i.finish(!0);
              })
            : (V.error("No such action:", t), i.finish(!0));
        },
        (e, i) => {
          V.error(i, "Error during action:", t), e.finish(!0);
        }
      );
    }
    initEmbed(e) {
      let t = Ag(e),
        i = null,
        n = this;
      Ch(() => {
        let e = A("commandLoop"),
          r = Jo().getScheduler();
        return (
          (n.hyperlinkListener = (e) => {
            let t = e,
              i =
                "#" === t.href.charAt(0) ||
                n.packageURL.some((e) => t.href.substr(0, e.length) == e);
            if (i) {
              e.preventDefault();
              let s = { t: "hyperlink", href: t.href, internal: i };
              r.run(() => (n.callback(s), T(!0)));
            }
          }),
          e
            .loopWithFrame((e) => {
              if (n.needResize)
                n.resize().then(() => {
                  e.continueLoop();
                });
              else if (n.needRefresh)
                n.currentPage &&
                  n
                    .showCurrent(n.currentPage, !this.renderAllPages)
                    .then(() => {
                      e.continueLoop();
                    });
              else if (t) {
                let i = t;
                (t = null),
                  n.runCommand(i).then(() => {
                    e.continueLoop();
                  });
              } else {
                let t = A("waitForCommand");
                (i = t.suspend(this)),
                  t.result().then(() => {
                    e.continueLoop();
                  });
              }
            })
            .thenFinish(e),
          e.result()
        );
      }),
        (n.kick = () => {
          let e = i;
          e && ((i = null), e.schedule(!0));
        }),
        (n.sendCommand = (e) => !t && ((t = Ag(e)), n.kick(), !0)),
        (this.window.adapt_command = n.sendCommand);
    }
  },
  Ap = ((e) => ((e.FIT_INSIDE_VIEWPORT = "fit inside viewport"), e))(Ap || {}),
  dl = class e extends Error {
    constructor() {
      super(),
        p(this, "name", "RenderingCanceledError"),
        p(this, "message", "Page rendering has been canceled"),
        p(this, "stack"),
        Object.setPrototypeOf(this, e.prototype),
        (this.stack = new Error().stack);
    }
  };
function Ag(e) {
  return "string" == typeof e ? Xs(e) : e;
}
var Lg = as;
function Db() {
  return {
    autoResize: !0,
    fontSize: 16,
    pageBorderWidth: 1,
    renderAllPages: !0,
    pageViewMode: "autoSpread",
    zoom: 1,
    fitToScreen: !1,
    defaultPaperSize: void 0,
    allowScripts: !0,
    pixelRatio: 8,
  };
}
function Rg(e) {
  let t = {};
  return (
    Object.keys(e).forEach((i) => {
      let n = e[i];
      switch (i) {
        case "autoResize":
          t.autoresize = n;
          break;
        case "pageBorderWidth":
          t.pageBorder = n;
          break;
        default:
          t[i] = n;
      }
    }),
    t
  );
}
var Zi = class {
  constructor(e, t) {
    (this.settings = e),
      p(this, "initialized", !1),
      p(this, "adaptViewer_"),
      p(this, "options"),
      p(this, "eventTarget"),
      p(this, "readyState"),
      xl(e.debug),
      (this.adaptViewer_ = new ul(
        e.window || window,
        e.viewportElement,
        "main",
        this.dispatcher.bind(this)
      )),
      (this.options = Db()),
      t && this.setOptions(t),
      (this.eventTarget = new kn()),
      Object.defineProperty(this, "readyState", {
        get() {
          return this.adaptViewer_.readyState;
        },
      });
  }
  setOptions(e) {
    let t = Object.assign({ a: "configure" }, Rg(e));
    this.adaptViewer_.sendCommand(t), Object.assign(this.options, e);
  }
  dispatcher(e) {
    let t = { type: e.t },
      i = e;
    Object.keys(i).forEach((e) => {
      "t" !== e && (t[e] = i[e]);
    }),
      this.eventTarget.dispatchEvent(t);
  }
  addListener(e, t) {
    this.eventTarget.addEventListener(e, t, !1);
  }
  removeListener(e, t) {
    this.eventTarget.removeEventListener(e, t, !1);
  }
  loadDocument(e, t, i) {
    e &&
    (Array.isArray(e)
      ? e[0] && ("string" == typeof e[0] || e[0].url)
      : "string" == typeof e || e.url)
      ? this.loadDocumentOrPublication(e, null, t, i)
      : this.eventTarget.dispatchEvent({
          type: "error",
          content: { error: new Error("No URL specified") },
        });
  }
  loadPublication(e, t, i) {
    e
      ? this.loadDocumentOrPublication(null, e, t, i)
      : this.eventTarget.dispatchEvent({
          type: "error",
          content: { error: new Error("No URL specified") },
        });
  }
  loadDocumentOrPublication(e, t, i, n) {
    let r = i || {};
    function s(e) {
      if (e)
        return e.map((e) => ({ url: e.url || null, text: e.text || null }));
    }
    let o = s(r.authorStyleSheet),
      a = s(r.userStyleSheet);
    n && Object.assign(this.options, n);
    let l = Object.assign(
      {
        a: e ? "loadXML" : "loadPublication",
        userAgentRootURL: this.settings.userAgentRootURL,
        url: Mb(e) || t,
        document: r.documentObject,
        fragment: r.fragment,
        authorStyleSheet: o,
        userStyleSheet: a,
      },
      Rg(this.options)
    );
    this.initialized
      ? this.adaptViewer_.sendCommand(l)
      : ((this.initialized = !0), this.adaptViewer_.initEmbed(l));
  }
  getCurrentPageProgression() {
    return this.adaptViewer_.getCurrentPageProgression();
  }
  resolveNavigation(e) {
    switch (e) {
      case "left":
        return this.getCurrentPageProgression() === Lg.LTR
          ? "previous"
          : "next";
      case "right":
        return this.getCurrentPageProgression() === Lg.LTR
          ? "next"
          : "previous";
      default:
        return e;
    }
  }
  navigateToPage(e, t) {
    "epage" === e
      ? this.adaptViewer_.sendCommand({ a: "moveTo", epage: t })
      : this.adaptViewer_.sendCommand({
          a: "moveTo",
          where: this.resolveNavigation(e),
        });
  }
  navigateToInternalUrl(e) {
    this.adaptViewer_.sendCommand({ a: "moveTo", url: e });
  }
  navigateToPosition(e) {
    var t, i;
    this.adaptViewer_.sendCommand({
      a: "moveTo",
      position: {
        spineIndex: e.spineIndex,
        pageIndex: null != (t = e.pageIndex) ? t : -1,
        offsetInItem: null != (i = e.offsetInItem) ? i : -1,
      },
    });
  }
  isTOCVisible() {
    return this.adaptViewer_.opfView &&
      this.adaptViewer_.opfView.opf &&
      this.adaptViewer_.opfView.opf.toc
      ? !!this.adaptViewer_.opfView.isTOCVisible()
      : null;
  }
  showTOC(e, t) {
    let i = null == e ? "toggle" : e ? "show" : "hide";
    this.adaptViewer_.sendCommand({ a: "toc", v: i, autohide: t });
  }
  queryZoomFactor(e) {
    return this.adaptViewer_.queryZoomFactor(e);
  }
  getPageSizes() {
    return this.adaptViewer_.pageSizes;
  }
  getTOC() {
    var e, t;
    return null ==
      (t = null == (e = this.adaptViewer_.opfView) ? void 0 : e.tocView)
      ? void 0
      : t.getTOC();
  }
  getMetadata() {
    return this.adaptViewer_.opf.getMetadata();
  }
  getCover() {
    return this.adaptViewer_.opf.cover;
  }
};
function Mb(e) {
  function t(e) {
    return "number" == typeof e ? e : null;
  }
  function i(e) {
    return "string" == typeof e
      ? { url: e, startPage: null, skipPagesBefore: null }
      : {
          url: e.url,
          startPage: t(e.startPage),
          skipPagesBefore: t(e.skipPagesBefore),
        };
  }
  return Array.isArray(e) ? e.map(i) : e ? [i(e)] : null;
}
var Ig = ((e) => (
    (e.PREVIOUS = "previous"),
    (e.NEXT = "next"),
    (e.LEFT = "left"),
    (e.RIGHT = "right"),
    (e.FIRST = "first"),
    (e.LAST = "last"),
    (e.EPAGE = "epage"),
    e
  ))(Ig || {}),
  _b = Ap,
  Ub = kp;
Ye.forceRegisterEndTiming("load_vivliostyle");
var Lp = class {
  constructor(
    e,
    {
      title: t = "",
      printCallback: i = (e) => e.print(),
      errorCallback: n = null,
      hideIframe: r = !0,
      removeIframe: s = !0,
    }
  ) {
    p(this, "htmlDoc"),
      p(this, "title"),
      p(this, "printCallback"),
      p(this, "errorCallback"),
      p(this, "hideIframe"),
      p(this, "removeIframe"),
      p(this, "iframe"),
      p(this, "iframeWin"),
      p(this, "window"),
      (this.htmlDoc = e),
      (this.title = t),
      (this.printCallback = i),
      (this.errorCallback = n),
      (this.hideIframe = r),
      (this.removeIframe = s);
  }
  init() {
    (this.iframe = document.createElement("iframe")),
      this.hideIframe &&
        ((this.iframe.style.width = "0"),
        (this.iframe.style.height = "0"),
        (this.iframe.style.borderWidth = "0")),
      (this.window = window),
      (this.window.printInstance = this),
      (this.iframe.srcdoc = `\n      <!DOCTYPE html>\n      <html data-vivliostyle-paginated="true">\n        <head>\n          <meta charset='utf-8'/>\n          <meta name='viewport' content='width=device-width, initial-scale=1.0'/>\n          <title>${this.title}</title>\n          <style>\n            html[data-vivliostyle-paginated] {\n              width: 100%;\n              height: 100%;\n            }\n            html[data-vivliostyle-paginated] body,\n            html[data-vivliostyle-paginated] [data-vivliostyle-viewer-viewport] {\n              width: 100% !important;\n              height: 100% !important;\n            }\n            html[data-vivliostyle-paginated],\n            html[data-vivliostyle-paginated] body {\n              margin: 0;\n              padding: 0;\n            }\n          </style>\n          <style id='vivliostyle-page-rules'></style>\n        </head>\n        <body onload='parent.printInstance.runInIframe(window)'>\n          <div id="vivliostyle-viewer-viewport"></div>\n        </body>\n      </html>`),
      document.body.appendChild(this.iframe);
  }
  runInIframe(e) {
    return (
      (this.iframeWin = e),
      this.preparePrint()
        .then(() => this.browserPrint())
        .then(() => this.cleanUp())
    );
  }
  preparePrint() {
    this.iframeWin.document.title = this.title;
    let e = new Blob([this.htmlDoc], { type: "text/html" }),
      t = URL.createObjectURL(e),
      i = new Zi({
        viewportElement: this.iframeWin.document.body.firstElementChild,
        window: this.iframeWin,
        debug: !0,
      });
    return new Promise((e) => {
      i.addListener("readystatechange", () => {
        "complete" === i.readyState && e();
      }),
        this.errorCallback &&
          i.addListener("error", (e) => {
            var t, i;
            let n =
              null !=
              (i = null == (t = e.content.error) ? void 0 : t.toString())
                ? i
                : e.content.messages.join("\n");
            this.errorCallback(n);
          }),
        i.loadDocument({ url: t });
    });
  }
  browserPrint() {
    this.printCallback(this.iframeWin);
  }
  cleanUp() {
    delete this.window.printInstance,
      this.removeIframe && this.iframe.parentElement.removeChild(this.iframe);
  }
};
function Hb(e, t) {
  new Lp(e, t).init();
}
//# sourceMappingURL=/sm/d456213ab09fa766ff77c619faf8cf03d1b985877efe6e9f72a03b49024e4716.map
