---
layout: blog_contents
title: Reddit评论排序算法
categories: blog
---

Reddit评论排序新算法：解决前10楼占领优秀评论区的问题  
```javascript    
n = “支持” + “反对”;
if (n==0) {
    score = 0;
} else {
    z = 1.96;
    phat = “支持” / n;
    score = (phat + z*z/(2*n) - z * Math.sqrt((phat*(1-phat)+z*z/(4*n))/n))/(1+z*z/n);
}
```