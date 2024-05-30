// ==UserScript==
// @name         2FA.OTP.TOTP
// @namespace    https://toknot.com/
// @version      2024-05-30
// @description  For 2FA base TOTP
// @author       chopin xiao
// @match        http://127.0.0.1/my2fa.html
// @match        file:///*/my2fa.html
// @match        https://page.toknot.com/*
// @icon         data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==
// @updateURL    https://toknot.com/download/2fa.meta.js
// @@downloadURL https://toknot.com/download/2fa.js
// @grant GM_setValue
// @grant GM_getValue
// @grant GM.setValue
// @grant GM.getValue
// @grant GM_listValues
// @grant GM_deleteValue
// @grant GM_setClipboard
// @run-at document-end
// @copyright   Toknot.com
// ==/UserScript==

(function (L, P) { typeof exports == "object" && typeof module != "undefined" ? P(exports) : typeof define == "function" && define.amd ? define(["exports"], P) : (L = typeof globalThis != "undefined" ? globalThis : L || self, P(L.OTPAuth = {})) })(this, function (L) { "use strict"; const P = i => { const e = new ArrayBuffer(8), n = new Uint8Array(e); let t = i; for (let r = 7; r >= 0 && t !== 0; r--)n[r] = t & 255, t -= n[r], t /= 256; return e }; var Z = Object.freeze({ __proto__: null, createHmac: void 0, randomBytes: void 0, timingSafeEqual: void 0 }); const W = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"; function _(i, e, n, t) { let r, s, u; const c = e || [0], a = (n = n || 0) >>> 3, f = t === -1 ? 3 : 0; for (r = 0; r < i.length; r += 1)u = r + a, s = u >>> 2, c.length <= s && c.push(0), c[s] |= i[r] << 8 * (f + t * (u % 4)); return { value: c, binLen: 8 * i.length + n } } function F(i, e, n) { switch (e) { case "UTF8": case "UTF16BE": case "UTF16LE": break; default: throw new Error("encoding must be UTF8, UTF16BE, or UTF16LE") }switch (i) { case "HEX": return function (t, r, s) { return function (u, c, a, f) { let d, l, h, g; if (u.length % 2 != 0) throw new Error("String of HEX type must be in byte increments"); const p = c || [0], m = (a = a || 0) >>> 3, A = f === -1 ? 3 : 0; for (d = 0; d < u.length; d += 2) { if (l = parseInt(u.substr(d, 2), 16), isNaN(l)) throw new Error("String of HEX type contains invalid characters"); for (g = (d >>> 1) + m, h = g >>> 2; p.length <= h;)p.push(0); p[h] |= l << 8 * (A + f * (g % 4)) } return { value: p, binLen: 4 * u.length + a } }(t, r, s, n) }; case "TEXT": return function (t, r, s) { return function (u, c, a, f, d) { let l, h, g, p, m, A, b, C, I = 0; const H = a || [0], S = (f = f || 0) >>> 3; if (c === "UTF8") for (b = d === -1 ? 3 : 0, g = 0; g < u.length; g += 1)for (l = u.charCodeAt(g), h = [], 128 > l ? h.push(l) : 2048 > l ? (h.push(192 | l >>> 6), h.push(128 | 63 & l)) : 55296 > l || 57344 <= l ? h.push(224 | l >>> 12, 128 | l >>> 6 & 63, 128 | 63 & l) : (g += 1, l = 65536 + ((1023 & l) << 10 | 1023 & u.charCodeAt(g)), h.push(240 | l >>> 18, 128 | l >>> 12 & 63, 128 | l >>> 6 & 63, 128 | 63 & l)), p = 0; p < h.length; p += 1) { for (A = I + S, m = A >>> 2; H.length <= m;)H.push(0); H[m] |= h[p] << 8 * (b + d * (A % 4)), I += 1 } else for (b = d === -1 ? 2 : 0, C = c === "UTF16LE" && d !== 1 || c !== "UTF16LE" && d === 1, g = 0; g < u.length; g += 1) { for (l = u.charCodeAt(g), C === !0 && (p = 255 & l, l = p << 8 | l >>> 8), A = I + S, m = A >>> 2; H.length <= m;)H.push(0); H[m] |= l << 8 * (b + d * (A % 4)), I += 2 } return { value: H, binLen: 8 * I + f } }(t, e, r, s, n) }; case "B64": return function (t, r, s) { return function (u, c, a, f) { let d, l, h, g, p, m, A, b = 0; const C = c || [0], I = (a = a || 0) >>> 3, H = f === -1 ? 3 : 0, S = u.indexOf("="); if (u.search(/^[a-zA-Z0-9=+/]+$/) === -1) throw new Error("Invalid character in base-64 string"); if (u = u.replace(/=/g, ""), S !== -1 && S < u.length) throw new Error("Invalid '=' found in base-64 string"); for (l = 0; l < u.length; l += 4) { for (p = u.substr(l, 4), g = 0, h = 0; h < p.length; h += 1)d = W.indexOf(p.charAt(h)), g |= d << 18 - 6 * h; for (h = 0; h < p.length - 1; h += 1) { for (A = b + I, m = A >>> 2; C.length <= m;)C.push(0); C[m] |= (g >>> 16 - 8 * h & 255) << 8 * (H + f * (A % 4)), b += 1 } } return { value: C, binLen: 8 * b + a } }(t, r, s, n) }; case "BYTES": return function (t, r, s) { return function (u, c, a, f) { let d, l, h, g; const p = c || [0], m = (a = a || 0) >>> 3, A = f === -1 ? 3 : 0; for (l = 0; l < u.length; l += 1)d = u.charCodeAt(l), g = l + m, h = g >>> 2, p.length <= h && p.push(0), p[h] |= d << 8 * (A + f * (g % 4)); return { value: p, binLen: 8 * u.length + a } }(t, r, s, n) }; case "ARRAYBUFFER": try { new ArrayBuffer(0) } catch (t) { throw new Error("ARRAYBUFFER not supported by this environment") } return function (t, r, s) { return function (u, c, a, f) { return _(new Uint8Array(u), c, a, f) }(t, r, s, n) }; case "UINT8ARRAY": try { new Uint8Array(0) } catch (t) { throw new Error("UINT8ARRAY not supported by this environment") } return function (t, r, s) { return _(t, r, s, n) }; default: throw new Error("format must be HEX, TEXT, B64, BYTES, ARRAYBUFFER, or UINT8ARRAY") } } function ee(i, e, n, t) { switch (i) { case "HEX": return function (r) { return function (s, u, c, a) { const f = "0123456789abcdef"; let d, l, h = ""; const g = u / 8, p = c === -1 ? 3 : 0; for (d = 0; d < g; d += 1)l = s[d >>> 2] >>> 8 * (p + c * (d % 4)), h += f.charAt(l >>> 4 & 15) + f.charAt(15 & l); return a.outputUpper ? h.toUpperCase() : h }(r, e, n, t) }; case "B64": return function (r) { return function (s, u, c, a) { let f, d, l, h, g, p = ""; const m = u / 8, A = c === -1 ? 3 : 0; for (f = 0; f < m; f += 3)for (h = f + 1 < m ? s[f + 1 >>> 2] : 0, g = f + 2 < m ? s[f + 2 >>> 2] : 0, l = (s[f >>> 2] >>> 8 * (A + c * (f % 4)) & 255) << 16 | (h >>> 8 * (A + c * ((f + 1) % 4)) & 255) << 8 | g >>> 8 * (A + c * ((f + 2) % 4)) & 255, d = 0; d < 4; d += 1)p += 8 * f + 6 * d <= u ? W.charAt(l >>> 6 * (3 - d) & 63) : a.b64Pad; return p }(r, e, n, t) }; case "BYTES": return function (r) { return function (s, u, c) { let a, f, d = ""; const l = u / 8, h = c === -1 ? 3 : 0; for (a = 0; a < l; a += 1)f = s[a >>> 2] >>> 8 * (h + c * (a % 4)) & 255, d += String.fromCharCode(f); return d }(r, e, n) }; case "ARRAYBUFFER": try { new ArrayBuffer(0) } catch (r) { throw new Error("ARRAYBUFFER not supported by this environment") } return function (r) { return function (s, u, c) { let a; const f = u / 8, d = new ArrayBuffer(f), l = new Uint8Array(d), h = c === -1 ? 3 : 0; for (a = 0; a < f; a += 1)l[a] = s[a >>> 2] >>> 8 * (h + c * (a % 4)) & 255; return d }(r, e, n) }; case "UINT8ARRAY": try { new Uint8Array(0) } catch (r) { throw new Error("UINT8ARRAY not supported by this environment") } return function (r) { return function (s, u, c) { let a; const f = u / 8, d = c === -1 ? 3 : 0, l = new Uint8Array(f); for (a = 0; a < f; a += 1)l[a] = s[a >>> 2] >>> 8 * (d + c * (a % 4)) & 255; return l }(r, e, n) }; default: throw new Error("format must be HEX, B64, BYTES, ARRAYBUFFER, or UINT8ARRAY") } } const w = [1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221, 3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580, 3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986, 2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895, 666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037, 2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344, 430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779, 1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298], $ = [3238371032, 914150663, 812702999, 4144912697, 4290775857, 1750603025, 1694076839, 3204075428], B = [1779033703, 3144134277, 1013904242, 2773480762, 1359893119, 2600822924, 528734635, 1541459225], O = "Chosen SHA variant is not supported"; function z(i, e) { let n, t; const r = i.binLen >>> 3, s = e.binLen >>> 3, u = r << 3, c = 4 - r << 3; if (r % 4 != 0) { for (n = 0; n < s; n += 4)t = r + n >>> 2, i.value[t] |= e.value[n >>> 2] << u, i.value.push(0), i.value[t + 1] |= e.value[n >>> 2] >>> c; return (i.value.length << 2) - 4 >= s + r && i.value.pop(), { value: i.value, binLen: i.binLen + e.binLen } } return { value: i.value.concat(e.value), binLen: i.binLen + e.binLen } } function te(i) { const e = { outputUpper: !1, b64Pad: "=", outputLen: -1 }, n = i || {}, t = "Output length must be a multiple of 8"; if (e.outputUpper = n.outputUpper || !1, n.b64Pad && (e.b64Pad = n.b64Pad), n.outputLen) { if (n.outputLen % 8 != 0) throw new Error(t); e.outputLen = n.outputLen } else if (n.shakeLen) { if (n.shakeLen % 8 != 0) throw new Error(t); e.outputLen = n.shakeLen } if (typeof e.outputUpper != "boolean") throw new Error("Invalid outputUpper formatting option"); if (typeof e.b64Pad != "string") throw new Error("Invalid b64Pad formatting option"); return e } function K(i, e, n, t) { const r = i + " must include a value and format"; if (!e) { if (!t) throw new Error(r); return t } if (e.value === void 0 || !e.format) throw new Error(r); return F(e.format, e.encoding || "UTF8", n)(e.value) } let D = class { constructor(e, n, t) { const r = t || {}; if (this.t = n, this.i = r.encoding || "UTF8", this.numRounds = r.numRounds || 1, isNaN(this.numRounds) || this.numRounds !== parseInt(this.numRounds, 10) || 1 > this.numRounds) throw new Error("numRounds must a integer >= 1"); this.o = e, this.h = [], this.u = 0, this.l = !1, this.A = 0, this.H = !1, this.S = [], this.p = [] } update(e) { let n, t = 0; const r = this.m >>> 5, s = this.C(e, this.h, this.u), u = s.binLen, c = s.value, a = u >>> 5; for (n = 0; n < a; n += r)t + this.m <= u && (this.R = this.U(c.slice(n, n + r), this.R), t += this.m); return this.A += t, this.h = c.slice(t >>> 5), this.u = u % this.m, this.l = !0, this } getHash(e, n) { let t, r, s = this.v; const u = te(n); if (this.K) { if (u.outputLen === -1) throw new Error("Output length must be specified in options"); s = u.outputLen } const c = ee(e, s, this.T, u); if (this.H && this.F) return c(this.F(u)); for (r = this.g(this.h.slice(), this.u, this.A, this.B(this.R), s), t = 1; t < this.numRounds; t += 1)this.K && s % 32 != 0 && (r[r.length - 1] &= 16777215 >>> 24 - s % 32), r = this.g(r, s, 0, this.L(this.o), s); return c(r) } setHMACKey(e, n, t) { if (!this.M) throw new Error("Variant does not support HMAC"); if (this.l) throw new Error("Cannot set MAC key after calling update"); const r = F(n, (t || {}).encoding || "UTF8", this.T); this.k(r(e)) } k(e) { const n = this.m >>> 3, t = n / 4 - 1; let r; if (this.numRounds !== 1) throw new Error("Cannot set numRounds with MAC"); if (this.H) throw new Error("MAC key already set"); for (n < e.binLen / 8 && (e.value = this.g(e.value, e.binLen, 0, this.L(this.o), this.v)); e.value.length <= t;)e.value.push(0); for (r = 0; r <= t; r += 1)this.S[r] = 909522486 ^ e.value[r], this.p[r] = 1549556828 ^ e.value[r]; this.R = this.U(this.S, this.R), this.A = this.m, this.H = !0 } getHMAC(e, n) { const t = te(n); return ee(e, this.v, this.T, t)(this.Y()) } Y() { let e; if (!this.H) throw new Error("Cannot call getHMAC without first setting MAC key"); const n = this.g(this.h.slice(), this.u, this.A, this.B(this.R), this.v); return e = this.U(this.p, this.L(this.o)), e = this.g(n, this.v, this.m, e, this.v), e } }; function M(i, e) { return i << e | i >>> 32 - e } function R(i, e) { return i >>> e | i << 32 - e } function ne(i, e) { return i >>> e } function re(i, e, n) { return i ^ e ^ n } function ie(i, e, n) { return i & e ^ ~i & n } function se(i, e, n) { return i & e ^ i & n ^ e & n } function Ae(i) { return R(i, 2) ^ R(i, 13) ^ R(i, 22) } function y(i, e) { const n = (65535 & i) + (65535 & e); return (65535 & (i >>> 16) + (e >>> 16) + (n >>> 16)) << 16 | 65535 & n } function be(i, e, n, t) { const r = (65535 & i) + (65535 & e) + (65535 & n) + (65535 & t); return (65535 & (i >>> 16) + (e >>> 16) + (n >>> 16) + (t >>> 16) + (r >>> 16)) << 16 | 65535 & r } function Y(i, e, n, t, r) { const s = (65535 & i) + (65535 & e) + (65535 & n) + (65535 & t) + (65535 & r); return (65535 & (i >>> 16) + (e >>> 16) + (n >>> 16) + (t >>> 16) + (r >>> 16) + (s >>> 16)) << 16 | 65535 & s } function Ie(i) { return R(i, 7) ^ R(i, 18) ^ ne(i, 3) } function ye(i) { return R(i, 6) ^ R(i, 11) ^ R(i, 25) } function ve(i) { return [1732584193, 4023233417, 2562383102, 271733878, 3285377520] } function oe(i, e) { let n, t, r, s, u, c, a; const f = []; for (n = e[0], t = e[1], r = e[2], s = e[3], u = e[4], a = 0; a < 80; a += 1)f[a] = a < 16 ? i[a] : M(f[a - 3] ^ f[a - 8] ^ f[a - 14] ^ f[a - 16], 1), c = a < 20 ? Y(M(n, 5), ie(t, r, s), u, 1518500249, f[a]) : a < 40 ? Y(M(n, 5), re(t, r, s), u, 1859775393, f[a]) : a < 60 ? Y(M(n, 5), se(t, r, s), u, 2400959708, f[a]) : Y(M(n, 5), re(t, r, s), u, 3395469782, f[a]), u = s, s = r, r = M(t, 30), t = n, n = c; return e[0] = y(n, e[0]), e[1] = y(t, e[1]), e[2] = y(r, e[2]), e[3] = y(s, e[3]), e[4] = y(u, e[4]), e } function Ee(i, e, n, t) { let r; const s = 15 + (e + 65 >>> 9 << 4), u = e + n; for (; i.length <= s;)i.push(0); for (i[e >>> 5] |= 128 << 24 - e % 32, i[s] = 4294967295 & u, i[s - 1] = u / 4294967296 | 0, r = 0; r < i.length; r += 16)t = oe(i.slice(r, r + 16), t); return t } class He extends D { constructor(e, n, t) { if (e !== "SHA-1") throw new Error(O); super(e, n, t); const r = t || {}; this.M = !0, this.F = this.Y, this.T = -1, this.C = F(this.t, this.i, this.T), this.U = oe, this.B = function (s) { return s.slice() }, this.L = ve, this.g = Ee, this.R = [1732584193, 4023233417, 2562383102, 271733878, 3285377520], this.m = 512, this.v = 160, this.K = !1, r.hmacKey && this.k(K("hmacKey", r.hmacKey, this.T)) } } function ue(i) { let e; return e = i == "SHA-224" ? $.slice() : B.slice(), e } function he(i, e) { let n, t, r, s, u, c, a, f, d, l, h; const g = []; for (n = e[0], t = e[1], r = e[2], s = e[3], u = e[4], c = e[5], a = e[6], f = e[7], h = 0; h < 64; h += 1)g[h] = h < 16 ? i[h] : be(R(p = g[h - 2], 17) ^ R(p, 19) ^ ne(p, 10), g[h - 7], Ie(g[h - 15]), g[h - 16]), d = Y(f, ye(u), ie(u, c, a), w[h], g[h]), l = y(Ae(n), se(n, t, r)), f = a, a = c, c = u, u = y(s, d), s = r, r = t, t = n, n = y(d, l); var p; return e[0] = y(n, e[0]), e[1] = y(t, e[1]), e[2] = y(r, e[2]), e[3] = y(s, e[3]), e[4] = y(u, e[4]), e[5] = y(c, e[5]), e[6] = y(a, e[6]), e[7] = y(f, e[7]), e } class Ne extends D { constructor(e, n, t) { if (e !== "SHA-224" && e !== "SHA-256") throw new Error(O); super(e, n, t); const r = t || {}; this.F = this.Y, this.M = !0, this.T = -1, this.C = F(this.t, this.i, this.T), this.U = he, this.B = function (s) { return s.slice() }, this.L = ue, this.g = function (s, u, c, a) { return function (f, d, l, h, g) { let p, m; const A = 15 + (d + 65 >>> 9 << 4), b = d + l; for (; f.length <= A;)f.push(0); for (f[d >>> 5] |= 128 << 24 - d % 32, f[A] = 4294967295 & b, f[A - 1] = b / 4294967296 | 0, p = 0; p < f.length; p += 16)h = he(f.slice(p, p + 16), h); return m = g === "SHA-224" ? [h[0], h[1], h[2], h[3], h[4], h[5], h[6]] : h, m }(s, u, c, a, e) }, this.R = ue(e), this.m = 512, this.v = e === "SHA-224" ? 224 : 256, this.K = !1, r.hmacKey && this.k(K("hmacKey", r.hmacKey, this.T)) } } class o { constructor(e, n) { this.N = e, this.I = n } } function ae(i, e) { let n; return e > 32 ? (n = 64 - e, new o(i.I << e | i.N >>> n, i.N << e | i.I >>> n)) : e !== 0 ? (n = 32 - e, new o(i.N << e | i.I >>> n, i.I << e | i.N >>> n)) : i } function T(i, e) { let n; return e < 32 ? (n = 32 - e, new o(i.N >>> e | i.I << n, i.I >>> e | i.N << n)) : (n = 64 - e, new o(i.I >>> e | i.N << n, i.N >>> e | i.I << n)) } function ce(i, e) { return new o(i.N >>> e, i.I >>> e | i.N << 32 - e) } function Se(i, e, n) { return new o(i.N & e.N ^ i.N & n.N ^ e.N & n.N, i.I & e.I ^ i.I & n.I ^ e.I & n.I) } function Re(i) { const e = T(i, 28), n = T(i, 34), t = T(i, 39); return new o(e.N ^ n.N ^ t.N, e.I ^ n.I ^ t.I) } function N(i, e) { let n, t; n = (65535 & i.I) + (65535 & e.I), t = (i.I >>> 16) + (e.I >>> 16) + (n >>> 16); const r = (65535 & t) << 16 | 65535 & n; return n = (65535 & i.N) + (65535 & e.N) + (t >>> 16), t = (i.N >>> 16) + (e.N >>> 16) + (n >>> 16), new o((65535 & t) << 16 | 65535 & n, r) } function Te(i, e, n, t) { let r, s; r = (65535 & i.I) + (65535 & e.I) + (65535 & n.I) + (65535 & t.I), s = (i.I >>> 16) + (e.I >>> 16) + (n.I >>> 16) + (t.I >>> 16) + (r >>> 16); const u = (65535 & s) << 16 | 65535 & r; return r = (65535 & i.N) + (65535 & e.N) + (65535 & n.N) + (65535 & t.N) + (s >>> 16), s = (i.N >>> 16) + (e.N >>> 16) + (n.N >>> 16) + (t.N >>> 16) + (r >>> 16), new o((65535 & s) << 16 | 65535 & r, u) } function Ue(i, e, n, t, r) { let s, u; s = (65535 & i.I) + (65535 & e.I) + (65535 & n.I) + (65535 & t.I) + (65535 & r.I), u = (i.I >>> 16) + (e.I >>> 16) + (n.I >>> 16) + (t.I >>> 16) + (r.I >>> 16) + (s >>> 16); const c = (65535 & u) << 16 | 65535 & s; return s = (65535 & i.N) + (65535 & e.N) + (65535 & n.N) + (65535 & t.N) + (65535 & r.N) + (u >>> 16), u = (i.N >>> 16) + (e.N >>> 16) + (n.N >>> 16) + (t.N >>> 16) + (r.N >>> 16) + (s >>> 16), new o((65535 & u) << 16 | 65535 & s, c) } function x(i, e) { return new o(i.N ^ e.N, i.I ^ e.I) } function Ce(i) { const e = T(i, 19), n = T(i, 61), t = ce(i, 6); return new o(e.N ^ n.N ^ t.N, e.I ^ n.I ^ t.I) } function Le(i) { const e = T(i, 1), n = T(i, 8), t = ce(i, 7); return new o(e.N ^ n.N ^ t.N, e.I ^ n.I ^ t.I) } function $e(i) { const e = T(i, 14), n = T(i, 18), t = T(i, 41); return new o(e.N ^ n.N ^ t.N, e.I ^ n.I ^ t.I) } const Be = [new o(w[0], 3609767458), new o(w[1], 602891725), new o(w[2], 3964484399), new o(w[3], 2173295548), new o(w[4], 4081628472), new o(w[5], 3053834265), new o(w[6], 2937671579), new o(w[7], 3664609560), new o(w[8], 2734883394), new o(w[9], 1164996542), new o(w[10], 1323610764), new o(w[11], 3590304994), new o(w[12], 4068182383), new o(w[13], 991336113), new o(w[14], 633803317), new o(w[15], 3479774868), new o(w[16], 2666613458), new o(w[17], 944711139), new o(w[18], 2341262773), new o(w[19], 2007800933), new o(w[20], 1495990901), new o(w[21], 1856431235), new o(w[22], 3175218132), new o(w[23], 2198950837), new o(w[24], 3999719339), new o(w[25], 766784016), new o(w[26], 2566594879), new o(w[27], 3203337956), new o(w[28], 1034457026), new o(w[29], 2466948901), new o(w[30], 3758326383), new o(w[31], 168717936), new o(w[32], 1188179964), new o(w[33], 1546045734), new o(w[34], 1522805485), new o(w[35], 2643833823), new o(w[36], 2343527390), new o(w[37], 1014477480), new o(w[38], 1206759142), new o(w[39], 344077627), new o(w[40], 1290863460), new o(w[41], 3158454273), new o(w[42], 3505952657), new o(w[43], 106217008), new o(w[44], 3606008344), new o(w[45], 1432725776), new o(w[46], 1467031594), new o(w[47], 851169720), new o(w[48], 3100823752), new o(w[49], 1363258195), new o(w[50], 3750685593), new o(w[51], 3785050280), new o(w[52], 3318307427), new o(w[53], 3812723403), new o(w[54], 2003034995), new o(w[55], 3602036899), new o(w[56], 1575990012), new o(w[57], 1125592928), new o(w[58], 2716904306), new o(w[59], 442776044), new o(w[60], 593698344), new o(w[61], 3733110249), new o(w[62], 2999351573), new o(w[63], 3815920427), new o(3391569614, 3928383900), new o(3515267271, 566280711), new o(3940187606, 3454069534), new o(4118630271, 4000239992), new o(116418474, 1914138554), new o(174292421, 2731055270), new o(289380356, 3203993006), new o(460393269, 320620315), new o(685471733, 587496836), new o(852142971, 1086792851), new o(1017036298, 365543100), new o(1126000580, 2618297676), new o(1288033470, 3409855158), new o(1501505948, 4234509866), new o(1607167915, 987167468), new o(1816402316, 1246189591)]; function fe(i) { return i === "SHA-384" ? [new o(3418070365, $[0]), new o(1654270250, $[1]), new o(2438529370, $[2]), new o(355462360, $[3]), new o(1731405415, $[4]), new o(41048885895, $[5]), new o(3675008525, $[6]), new o(1203062813, $[7])] : [new o(B[0], 4089235720), new o(B[1], 2227873595), new o(B[2], 4271175723), new o(B[3], 1595750129), new o(B[4], 2917565137), new o(B[5], 725511199), new o(B[6], 4215389547), new o(B[7], 327033209)] } function le(i, e) { let n, t, r, s, u, c, a, f, d, l, h, g; const p = []; for (n = e[0], t = e[1], r = e[2], s = e[3], u = e[4], c = e[5], a = e[6], f = e[7], h = 0; h < 80; h += 1)h < 16 ? (g = 2 * h, p[h] = new o(i[g], i[g + 1])) : p[h] = Te(Ce(p[h - 2]), p[h - 7], Le(p[h - 15]), p[h - 16]), d = Ue(f, $e(u), (A = c, b = a, new o((m = u).N & A.N ^ ~m.N & b.N, m.I & A.I ^ ~m.I & b.I)), Be[h], p[h]), l = N(Re(n), Se(n, t, r)), f = a, a = c, c = u, u = N(s, d), s = r, r = t, t = n, n = N(d, l); var m, A, b; return e[0] = N(n, e[0]), e[1] = N(t, e[1]), e[2] = N(r, e[2]), e[3] = N(s, e[3]), e[4] = N(u, e[4]), e[5] = N(c, e[5]), e[6] = N(a, e[6]), e[7] = N(f, e[7]), e } class Ke extends D { constructor(e, n, t) { if (e !== "SHA-384" && e !== "SHA-512") throw new Error(O); super(e, n, t); const r = t || {}; this.F = this.Y, this.M = !0, this.T = -1, this.C = F(this.t, this.i, this.T), this.U = le, this.B = function (s) { return s.slice() }, this.L = fe, this.g = function (s, u, c, a) { return function (f, d, l, h, g) { let p, m; const A = 31 + (d + 129 >>> 10 << 5), b = d + l; for (; f.length <= A;)f.push(0); for (f[d >>> 5] |= 128 << 24 - d % 32, f[A] = 4294967295 & b, f[A - 1] = b / 4294967296 | 0, p = 0; p < f.length; p += 32)h = le(f.slice(p, p + 32), h); return m = g === "SHA-384" ? [h[0].N, h[0].I, h[1].N, h[1].I, h[2].N, h[2].I, h[3].N, h[3].I, h[4].N, h[4].I, h[5].N, h[5].I] : [h[0].N, h[0].I, h[1].N, h[1].I, h[2].N, h[2].I, h[3].N, h[3].I, h[4].N, h[4].I, h[5].N, h[5].I, h[6].N, h[6].I, h[7].N, h[7].I], m }(s, u, c, a, e) }, this.R = fe(e), this.m = 1024, this.v = e === "SHA-384" ? 384 : 512, this.K = !1, r.hmacKey && this.k(K("hmacKey", r.hmacKey, this.T)) } } const Fe = [new o(0, 1), new o(0, 32898), new o(2147483648, 32906), new o(2147483648, 2147516416), new o(0, 32907), new o(0, 2147483649), new o(2147483648, 2147516545), new o(2147483648, 32777), new o(0, 138), new o(0, 136), new o(0, 2147516425), new o(0, 2147483658), new o(0, 2147516555), new o(2147483648, 139), new o(2147483648, 32905), new o(2147483648, 32771), new o(2147483648, 32770), new o(2147483648, 128), new o(0, 32778), new o(2147483648, 2147483658), new o(2147483648, 2147516545), new o(2147483648, 32896), new o(0, 2147483649), new o(2147483648, 2147516424)], Me = [[0, 36, 3, 41, 18], [1, 44, 10, 45, 2], [62, 6, 43, 15, 61], [28, 55, 25, 21, 56], [27, 20, 39, 8, 14]]; function V(i) { let e; const n = []; for (e = 0; e < 5; e += 1)n[e] = [new o(0, 0), new o(0, 0), new o(0, 0), new o(0, 0), new o(0, 0)]; return n } function ke(i) { let e; const n = []; for (e = 0; e < 5; e += 1)n[e] = i[e].slice(); return n } function q(i, e) { let n, t, r, s; const u = [], c = []; if (i !== null) for (t = 0; t < i.length; t += 2)e[(t >>> 1) % 5][(t >>> 1) / 5 | 0] = x(e[(t >>> 1) % 5][(t >>> 1) / 5 | 0], new o(i[t + 1], i[t])); for (n = 0; n < 24; n += 1) { for (s = V(), t = 0; t < 5; t += 1)u[t] = (a = e[t][0], f = e[t][1], d = e[t][2], l = e[t][3], h = e[t][4], new o(a.N ^ f.N ^ d.N ^ l.N ^ h.N, a.I ^ f.I ^ d.I ^ l.I ^ h.I)); for (t = 0; t < 5; t += 1)c[t] = x(u[(t + 4) % 5], ae(u[(t + 1) % 5], 1)); for (t = 0; t < 5; t += 1)for (r = 0; r < 5; r += 1)e[t][r] = x(e[t][r], c[t]); for (t = 0; t < 5; t += 1)for (r = 0; r < 5; r += 1)s[r][(2 * t + 3 * r) % 5] = ae(e[t][r], Me[t][r]); for (t = 0; t < 5; t += 1)for (r = 0; r < 5; r += 1)e[t][r] = x(s[t][r], new o(~s[(t + 1) % 5][r].N & s[(t + 2) % 5][r].N, ~s[(t + 1) % 5][r].I & s[(t + 2) % 5][r].I)); e[0][0] = x(e[0][0], Fe[n]) } var a, f, d, l, h; return e } function we(i) { let e, n, t = 0; const r = [0, 0], s = [4294967295 & i, i / 4294967296 & 2097151]; for (e = 6; e >= 0; e--)n = s[e >> 2] >>> 8 * e & 255, n === 0 && t === 0 || (r[t + 1 >> 2] |= n << 8 * (t + 1), t += 1); return t = t !== 0 ? t : 1, r[0] |= t, { value: t + 1 > 4 ? r : [r[0]], binLen: 8 + 8 * t } } function J(i) { return z(we(i.binLen), i) } function de(i, e) { let n, t = we(e); t = z(t, i); const r = e >>> 2, s = (r - t.value.length % r) % r; for (n = 0; n < s; n++)t.value.push(0); return t.value } class Pe extends D { constructor(e, n, t) { let r = 6, s = 0; super(e, n, t); const u = t || {}; if (this.numRounds !== 1) { if (u.kmacKey || u.hmacKey) throw new Error("Cannot set numRounds with MAC"); if (this.o === "CSHAKE128" || this.o === "CSHAKE256") throw new Error("Cannot set numRounds for CSHAKE variants") } switch (this.T = 1, this.C = F(this.t, this.i, this.T), this.U = q, this.B = ke, this.L = V, this.R = V(), this.K = !1, e) { case "SHA3-224": this.m = s = 1152, this.v = 224, this.M = !0, this.F = this.Y; break; case "SHA3-256": this.m = s = 1088, this.v = 256, this.M = !0, this.F = this.Y; break; case "SHA3-384": this.m = s = 832, this.v = 384, this.M = !0, this.F = this.Y; break; case "SHA3-512": this.m = s = 576, this.v = 512, this.M = !0, this.F = this.Y; break; case "SHAKE128": r = 31, this.m = s = 1344, this.v = -1, this.K = !0, this.M = !1, this.F = null; break; case "SHAKE256": r = 31, this.m = s = 1088, this.v = -1, this.K = !0, this.M = !1, this.F = null; break; case "KMAC128": r = 4, this.m = s = 1344, this.X(t), this.v = -1, this.K = !0, this.M = !1, this.F = this._; break; case "KMAC256": r = 4, this.m = s = 1088, this.X(t), this.v = -1, this.K = !0, this.M = !1, this.F = this._; break; case "CSHAKE128": this.m = s = 1344, r = this.O(t), this.v = -1, this.K = !0, this.M = !1, this.F = null; break; case "CSHAKE256": this.m = s = 1088, r = this.O(t), this.v = -1, this.K = !0, this.M = !1, this.F = null; break; default: throw new Error(O) }this.g = function (c, a, f, d, l) { return function (h, g, p, m, A, b, C) { let I, H, S = 0; const j = [], G = A >>> 5, st = g >>> 5; for (I = 0; I < st && g >= A; I += G)m = q(h.slice(I, I + G), m), g -= A; for (h = h.slice(I), g %= A; h.length < G;)h.push(0); for (I = g >>> 3, h[I >> 2] ^= b << I % 4 * 8, h[G - 1] ^= 2147483648, m = q(h, m); 32 * j.length < C && (H = m[S % 5][S / 5 | 0], j.push(H.I), !(32 * j.length >= C));)j.push(H.N), S += 1, 64 * S % A == 0 && (q(null, m), S = 0); return j }(c, a, 0, d, s, r, l) }, u.hmacKey && this.k(K("hmacKey", u.hmacKey, this.T)) } O(e, n) { const t = function (s) { const u = s || {}; return { funcName: K("funcName", u.funcName, 1, { value: [], binLen: 0 }), customization: K("Customization", u.customization, 1, { value: [], binLen: 0 }) } }(e || {}); n && (t.funcName = n); const r = z(J(t.funcName), J(t.customization)); if (t.customization.binLen !== 0 || t.funcName.binLen !== 0) { const s = de(r, this.m >>> 3); for (let u = 0; u < s.length; u += this.m >>> 5)this.R = this.U(s.slice(u, u + (this.m >>> 5)), this.R), this.A += this.m; return 4 } return 31 } X(e) { const n = function (r) { const s = r || {}; return { kmacKey: K("kmacKey", s.kmacKey, 1), funcName: { value: [1128353099], binLen: 32 }, customization: K("Customization", s.customization, 1, { value: [], binLen: 0 }) } }(e || {}); this.O(e, n.funcName); const t = de(J(n.kmacKey), this.m >>> 3); for (let r = 0; r < t.length; r += this.m >>> 5)this.R = this.U(t.slice(r, r + (this.m >>> 5)), this.R), this.A += this.m; this.H = !0 } _(e) { const n = z({ value: this.h.slice(), binLen: this.u }, function (t) { let r, s, u = 0; const c = [0, 0], a = [4294967295 & t, t / 4294967296 & 2097151]; for (r = 6; r >= 0; r--)s = a[r >> 2] >>> 8 * r & 255, s === 0 && u === 0 || (c[u >> 2] |= s << 8 * u, u += 1); return u = u !== 0 ? u : 1, c[u >> 2] |= u << 8 * u, { value: u + 1 > 4 ? c : [c[0]], binLen: 8 + 8 * u } }(e.outputLen)); return this.g(n.value, n.binLen, this.A, this.B(this.R), e.outputLen) } } class Oe { constructor(e, n, t) { if (e == "SHA-1") this.P = new He(e, n, t); else if (e == "SHA-224" || e == "SHA-256") this.P = new Ne(e, n, t); else if (e == "SHA-384" || e == "SHA-512") this.P = new Ke(e, n, t); else { if (e != "SHA3-224" && e != "SHA3-256" && e != "SHA3-384" && e != "SHA3-512" && e != "SHAKE128" && e != "SHAKE256" && e != "CSHAKE128" && e != "CSHAKE256" && e != "KMAC128" && e != "KMAC256") throw new Error(O); this.P = new Pe(e, n, t) } } update(e) { return this.P.update(e), this } getHash(e, n) { return this.P.getHash(e, n) } setHMACKey(e, n, t) { this.P.setHMACKey(e, n, t) } getHMAC(e, n) { return this.P.getHMAC(e, n) } } const k = (() => { if (typeof globalThis == "object") return globalThis; Object.defineProperty(Object.prototype, "__GLOBALTHIS__", { get() { return this }, configurable: !0 }); try { if (typeof __GLOBALTHIS__ != "undefined") return __GLOBALTHIS__ } finally { delete Object.prototype.__GLOBALTHIS__ } if (typeof self != "undefined") return self; if (typeof window != "undefined") return window; if (typeof global != "undefined") return global })(), Ye = { SHA1: "SHA-1", SHA224: "SHA-224", SHA256: "SHA-256", SHA384: "SHA-384", SHA512: "SHA-512", "SHA3-224": "SHA3-224", "SHA3-256": "SHA3-256", "SHA3-384": "SHA3-384", "SHA3-512": "SHA3-512" }, xe = (i, e, n) => { Z != null; { const t = Ye[i.toUpperCase()]; if (typeof t == "undefined") throw new TypeError("Unknown hash function"); const r = new Oe(t, "ARRAYBUFFER"); return r.setHMACKey(e, "ARRAYBUFFER"), r.update(n), r.getHMAC("ARRAYBUFFER") } }, Q = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567", Xe = i => { let e = i.length; for (; i[e - 1] === "=";)--e; const n = (e < i.length ? i.substring(0, e) : i).toUpperCase(), t = new ArrayBuffer(n.length * 5 / 8 | 0), r = new Uint8Array(t); let s = 0, u = 0, c = 0; for (let a = 0; a < n.length; a++) { const f = Q.indexOf(n[a]); if (f === -1) throw new TypeError(`Invalid character found: ${n[a]}`); u = u << 5 | f, s += 5, s >= 8 && (s -= 8, r[c++] = u >>> s) } return t }, je = i => { const e = new Uint8Array(i); let n = 0, t = 0, r = ""; for (let s = 0; s < e.length; s++)for (t = t << 8 | e[s], n += 8; n >= 5;)r += Q[t >>> n - 5 & 31], n -= 5; return n > 0 && (r += Q[t << 5 - n & 31]), r }, ze = i => { const e = new ArrayBuffer(i.length / 2), n = new Uint8Array(e); for (let t = 0; t < i.length; t += 2)n[t / 2] = parseInt(i.substring(t, t + 2), 16); return e }, De = i => { const e = new Uint8Array(i); let n = ""; for (let t = 0; t < e.length; t++) { const r = e[t].toString(16); r.length === 1 && (n += "0"), n += r } return n.toUpperCase() }, qe = i => { const e = new ArrayBuffer(i.length), n = new Uint8Array(e); for (let t = 0; t < i.length; t++)n[t] = i.charCodeAt(t) & 255; return e }, Ge = i => { const e = new Uint8Array(i); let n = ""; for (let t = 0; t < e.length; t++)n += String.fromCharCode(e[t]); return n }, ge = k.TextEncoder ? new k.TextEncoder("utf-8") : null, pe = k.TextDecoder ? new k.TextDecoder("utf-8") : null, Ze = i => { if (!ge) throw new Error("Encoding API not available"); return ge.encode(i).buffer }, Ve = i => { if (!pe) throw new Error("Encoding API not available"); return pe.decode(i) }, Je = i => { var e; if (Z != null, !((e = k.crypto) != null && e.getRandomValues)) throw new Error("Cryptography API not available"); return k.crypto.getRandomValues(new Uint8Array(i)).buffer }; class U { constructor({ buffer: e, size: n = 20 } = {}) { this.buffer = typeof e == "undefined" ? Je(n) : e } static fromLatin1(e) { return new U({ buffer: qe(e) }) } static fromUTF8(e) { return new U({ buffer: Ze(e) }) } static fromBase32(e) { return new U({ buffer: Xe(e) }) } static fromHex(e) { return new U({ buffer: ze(e) }) } get latin1() { return Object.defineProperty(this, "latin1", { enumerable: !0, value: Ge(this.buffer) }), this.latin1 } get utf8() { return Object.defineProperty(this, "utf8", { enumerable: !0, value: Ve(this.buffer) }), this.utf8 } get base32() { return Object.defineProperty(this, "base32", { enumerable: !0, value: je(this.buffer) }), this.base32 } get hex() { return Object.defineProperty(this, "hex", { enumerable: !0, value: De(this.buffer) }), this.hex } } const Qe = (i, e) => { Z != null; { if (i.length !== e.length) throw new TypeError("Input strings must have the same length"); let n = -1, t = 0; for (; ++n < i.length;)t |= i.charCodeAt(n) ^ e.charCodeAt(n); return t === 0 } }; var We = Math.pow; let X = class v { static get defaults() { return { issuer: "", label: "OTPAuth", algorithm: "SHA1", digits: 6, counter: 0, window: 1 } } constructor({ issuer: e = v.defaults.issuer, label: n = v.defaults.label, secret: t = new U, algorithm: r = v.defaults.algorithm, digits: s = v.defaults.digits, counter: u = v.defaults.counter } = {}) { this.issuer = e, this.label = n, this.secret = typeof t == "string" ? U.fromBase32(t) : t, this.algorithm = r.toUpperCase(), this.digits = s, this.counter = u } static generate({ secret: e, algorithm: n = v.defaults.algorithm, digits: t = v.defaults.digits, counter: r = v.defaults.counter }) { const s = new Uint8Array(xe(n, e.buffer, P(r))), u = s[s.byteLength - 1] & 15; return (((s[u] & 127) << 24 | (s[u + 1] & 255) << 16 | (s[u + 2] & 255) << 8 | s[u + 3] & 255) % We(10, t)).toString().padStart(t, "0") } generate({ counter: e = this.counter++ } = {}) { return v.generate({ secret: this.secret, algorithm: this.algorithm, digits: this.digits, counter: e }) } static validate({ token: e, secret: n, algorithm: t, digits: r, counter: s = v.defaults.counter, window: u = v.defaults.window }) { if (e.length !== r) return null; let c = null; for (let a = s - u; a <= s + u; ++a) { const f = v.generate({ secret: n, algorithm: t, digits: r, counter: a }); Qe(e, f) && (c = a - s) } return c } validate({ token: e, counter: n = this.counter, window: t }) { return v.validate({ token: e, secret: this.secret, algorithm: this.algorithm, digits: this.digits, counter: n, window: t }) } toString() { const e = encodeURIComponent; return `otpauth://hotp/${this.issuer.length > 0 ? `${e(this.issuer)}:${e(this.label)}?issuer=${e(this.issuer)}&` : `${e(this.label)}?`}secret=${e(this.secret.base32)}&algorithm=${e(this.algorithm)}&digits=${e(this.digits)}&counter=${e(this.counter)}` } }; class E { static get defaults() { return { issuer: "", label: "OTPAuth", algorithm: "SHA1", digits: 6, period: 30, window: 1 } } constructor({ issuer: e = E.defaults.issuer, label: n = E.defaults.label, secret: t = new U, algorithm: r = E.defaults.algorithm, digits: s = E.defaults.digits, period: u = E.defaults.period } = {}) { this.issuer = e, this.label = n, this.secret = typeof t == "string" ? U.fromBase32(t) : t, this.algorithm = r.toUpperCase(), this.digits = s, this.period = u } static generate({ secret: e, algorithm: n, digits: t, period: r = E.defaults.period, timestamp: s = Date.now() }) { return X.generate({ secret: e, algorithm: n, digits: t, counter: Math.floor(s / 1e3 / r) }) } generate({ timestamp: e = Date.now() } = {}) { return E.generate({ secret: this.secret, algorithm: this.algorithm, digits: this.digits, period: this.period, timestamp: e }) } static validate({ token: e, secret: n, algorithm: t, digits: r, period: s = E.defaults.period, timestamp: u = Date.now(), window: c }) { return X.validate({ token: e, secret: n, algorithm: t, digits: r, counter: Math.floor(u / 1e3 / s), window: c }) } validate({ token: e, timestamp: n, window: t }) { return E.validate({ token: e, secret: this.secret, algorithm: this.algorithm, digits: this.digits, period: this.period, timestamp: n, window: t }) } toString() { const e = encodeURIComponent; return `otpauth://totp/${this.issuer.length > 0 ? `${e(this.issuer)}:${e(this.label)}?issuer=${e(this.issuer)}&` : `${e(this.label)}?`}secret=${e(this.secret.base32)}&algorithm=${e(this.algorithm)}&digits=${e(this.digits)}&period=${e(this.period)}` } } const _e = /^otpauth:\/\/([ht]otp)\/(.+)\?([A-Z0-9.~_-]+=[^?&]*(?:&[A-Z0-9.~_-]+=[^?&]*)*)$/i, et = /^[2-7A-Z]+=*$/i, tt = /^SHA(?:1|224|256|384|512|3-224|3-256|3-384|3-512)$/i, nt = /^[+-]?\d+$/, me = /^\+?[1-9]\d*$/; class rt { static parse(e) { let n; try { n = e.match(_e) } catch (a) { } if (!Array.isArray(n)) throw new URIError("Invalid URI format"); const t = n[1].toLowerCase(), r = n[2].split(/(?::|%3A) *(.+)/i, 2).map(decodeURIComponent), s = n[3].split("&").reduce((a, f) => { const d = f.split(/=(.*)/, 2).map(decodeURIComponent), l = d[0].toLowerCase(), h = d[1], g = a; return g[l] = h, g }, {}); let u; const c = {}; if (t === "hotp") if (u = X, typeof s.counter != "undefined" && nt.test(s.counter)) c.counter = parseInt(s.counter, 10); else throw new TypeError("Missing or invalid 'counter' parameter"); else if (t === "totp") { if (u = E, typeof s.period != "undefined") if (me.test(s.period)) c.period = parseInt(s.period, 10); else throw new TypeError("Invalid 'period' parameter") } else throw new TypeError("Unknown OTP type"); if (r.length === 2 ? (c.label = r[1], c.issuer = r[0]) : (c.label = r[0], typeof s.issuer != "undefined" && (c.issuer = s.issuer)), typeof s.secret != "undefined" && et.test(s.secret)) c.secret = s.secret; else throw new TypeError("Missing or invalid 'secret' parameter"); if (typeof s.algorithm != "undefined") if (tt.test(s.algorithm)) c.algorithm = s.algorithm; else throw new TypeError("Invalid 'algorithm' parameter"); if (typeof s.digits != "undefined") if (me.test(s.digits)) c.digits = parseInt(s.digits, 10); else throw new TypeError("Invalid 'digits' parameter"); return new u(c) } static stringify(e) { if (e instanceof X || e instanceof E) return e.toString(); throw new TypeError("Invalid 'HOTP/TOTP' object") } } const it = "9.1.4"; L.HOTP = X, L.Secret = U, L.TOTP = E, L.URI = rt, L.version = it });
//# sourceMappingURL=otpauth.umd.min.js.map

const secretkeys = GM_listValues();
let addInter = null;
function addCss() {
    let style = document.createElement('style');
    style.innerHTML = '.-m2fa-it {height: 3rem;width: 20rem;font-size: 1.5rem;margin: 1rem;} .-m2fa-btn{height: 3rem;width: 6rem;font-size: 1.5rem;margin: 1rem .3rem;}';
    document.head.appendChild(style);
    //let sheet = style.sheet;
    //sheet.insertRule('.-m2fa-it {height: 3rem;width: 20rem;font-size: 1.5rem;margin: 1rem;}', 0);
    //sheet.insertRule('.-m2fa-btn{height: 3rem;width: 20rem;font-size: 1.5rem;margin: 1rem;}', 0);

    let msg = document.createElement('div');
    msg.id = 'msg';
    msg.setAttribute('style', 'color: red; z-index: 1000;position: fixed;background-color: #FFF;width: 90%;text-align: center;top: 1;font-size: 1.5rem;');
    document.body.appendChild(msg);
}
function addGetCode(secret) {
    let code = getCodeJS(secret);
    document.getElementById('add-code-text').value = code;
}

function cleanIner(i) {
    if(i) {
        clearInterval(i);
    }
}

function msg(msg) {
    document.getElementById('msg').textContent = msg;
}
function add() {
    let box = document.createElement('div');
    box.id = 'add-box';
    box.innerHTML = '<div style="background-color: #FFF;width: 25rem;margin: 5rem auto;">'
        + '<input type="text" placeholder="名字" id="add-name" class="-m2fa-it"><br/>'
        + '<input type="text" placeholder="密钥" class="-m2fa-it" id="add-secret"><br/>'
        + '<input type="text" id="add-code-text" placeholder="自动生成的验证码" class="-m2fa-it" readonly><br/>'
        + '<button class="-m2fa-btn">确定</button>'
        + '<button class="-m2fa-btn">取消</button>'
        + '<br/></div>';
    box.setAttribute('style', 'position: absolute;z-index:100;height:100%;width:100%;top: 0;left: 0;background-color: rgba(3,3,3,0.8);');
    document.body.appendChild(box);
    let btns = box.getElementsByTagName('button');
    btns[0].addEventListener('click', function () {
        let secret = document.getElementById('add-secret').value;
        if (secret.length <= 5) {
            msg('没有密钥或太短');
            return;
        }
        let name = document.getElementById('add-name').value;
        if (name.length <= 2) {
            msg('没有名字或太短');
            return;
        }
        GM_setValue(name, secret);
        cleanIner(addInter);
        document.body.removeChild(document.getElementById('add-box'));
        window.location.reload();
    });
    btns[1].addEventListener('click', function () {
        cleanIner(addInter);
        document.body.removeChild(document.getElementById('add-box'));
    });
    let inpts = box.getElementsByTagName('input');
    inpts[0].addEventListener('change', function () {
        if (secretkeys.indexOf(this.value) >= 0) {
            msg('名字已经存在,将覆盖');
        } else {
            msg('');
        }
    });
    inpts[1].addEventListener('change', function () {
        let s = getLastSecond();
        let code = getCodeJS(this.value);
        let that = this;
        document.getElementById('add-code-text').value = code;
        msg('剩余时间:' + s);
        addInter = setInterval(function () {
            let code = getCodeJS(that.value);
            document.getElementById('add-code-text').value = code;
            s = getLastSecond();
            msg('剩余时间:' + s);
        }, 1000);

    });
    return;
}
function getCodeJS(secret) {
    try {
        const totp = new OTPAuth.TOTP({
            algorithm: 'SHA1',
            digits: 6,
            period: 30,
            secret: OTPAuth.Secret.fromBase32(secret)
        });
        return totp.generate();
    } catch (err) {
        msg(err.message);
    }
}

function createBtn(label, name, flag, click) {
    let btn = document.createElement('button');
    btn.textContent = label;
    btn.addEventListener('click', click);
    btn.name = name;
    btn.className = flag + ' -m2fa-btn';
    document.body.appendChild(btn);
    return btn;
}
function createText(label, name, flag, click) {
    let btn = document.createElement('input');
    btn.type = 'text';
    btn.value = label;
    btn.addEventListener('click', click);
    btn.name = name;
    btn.className = flag + ' -m2fa-it';
    btn.setAttribute('readonly', true);
    document.body.appendChild(btn);
    return btn;
}
function br() {
    let br = document.createElement('br');
    document.body.appendChild(br);
}
function copyCode() {
    let code = this.title;
    GM_setClipboard(code, 'text');
    prompt('已复制验证码:', code);
}
function delKey() {
    let key = this.name;
    if (confirm('确定删除' + key + '?')) {
        GM_deleteValue(key);
        window.location.reload();
    }
}
function getLastSecond() {
    let period = 30;
    return (period * (1 - ((Date.now() / 1000 / period) % 1))) | 0;
}
let timeInter = null;
function updateCode() {
    let t = document.querySelector('.time');
    if (timeInter) {
        clearInterval(timeInter);
    }
    let allkeys = document.querySelectorAll('.btnlist');
    timeInter = setInterval(function () {
        let lt = getLastSecond();
        t.value = '剩余时间:' + lt;
        for (let key of allkeys) {
            let secret = GM_getValue(key.name)
            let code = getCodeJS(secret);
            key.title = code;
            key.value = key.name + ':' + code;
        }
    }, 1000);
}
function save() {
    let allsecretkeys = {};
    for (let kn of secretkeys) {
        allsecretkeys[kn] = GM_getValue(kn);
    }
    var ks = JSON.stringify(allsecretkeys);
    const b = new File([ks], "2fa-key-bak.json", { type: "application/json" });
    var u = URL.createObjectURL(b);
    let a = document.createElement('a');
    a.href = u;
    a.setAttribute('download', "2fa-key-bak.json");
    a.click();
    delete a;
}
function importFile() {
    let i = document.createElement('input');
    i.setAttribute('type', "file");
    i.setAttribute('style', 'opacity: 0;display: inline;position: absolute;');
    document.body.appendChild(i);
    i.addEventListener("change", function () {
        let f = this.files[0];
        const reader = new FileReader();
        reader.addEventListener("load", () => {
            let data = JSON.parse(reader.result);
            for (let ik of Object.entries(data)) {
                GM_setValue(ik[0], ik[1]);
            }
            window.location.reload();
        }, false,);
        reader.readAsText(f);
    }, false);
    return i;
}
try {
    addCss();
    createBtn('添加', 'add', 'add', add);
    createBtn('备份', 'save', 'save', save);
    let inf = importFile();
    createBtn('导入', 'import', 'import', function () {
        inf.click();
    });

    createText('剩余时间:30', 30, 'time');
    br();
    for (let n of secretkeys) {
        let cbtn = createText(n, n, 'btnlist', copyCode);
        createBtn('删除', n, 'del', delKey);
        br();
    }

    updateCode();
} catch (err) {
    msg(err.message)
}
