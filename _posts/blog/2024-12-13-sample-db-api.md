---
layout: blog_contents
title: 一个API
categories: blog
---

* 表类型：
  * 数据临时表：数据会在一定的条件下被清理
  * 数据持久表：除除非主动清理不会变更
  * 可硬删除行数据
* 表字段
  * 字段类型：短字符，长文本，数字，时间
  * 字段属性：
    * 可获取
    * 可更新
    * 可创建
    * 自动生成
      * 软删除字段，使用时间
      * 行版本号
      * 行创建时间
      * 行更新时间
      * 随机短文本
  * 字段查询条件：比较查询，匹配查询，集合查询
  * 字段查询前置条件
* 多表字段条件查询
* API
  * 接口操作
    * POST /{table1},{table2}/create  创建
      * 请求Body
        ```json
        {
            "precondition" : {
                "token_feild" : "token"
            },
            "data" : {
                "table1" : {
                    "feild1" : "value1",
                    "feild2" : "value2"
                },
                "table2" : {
                    "feild1" : "value1",
                    "feild2" : "value2"
                }
            }
        }
        ```
    * POST /{table1},{table2}/update  更新
      * 请求Body
        ```json
        {
            "data" : {
                "table1" : {
                    "feild1" : "value1",
                    "feild2" : "value2"
                },
                "table2" : {
                    "feild1" : "value1",
                    "feild2" : "value2"
                }
            },
            "match" : {
                "table1" : {
                    "feild2" : [">", "value1"],
                },
                "table2" : {
                    "feild2" : ["=", "value1"],
                }
            }
        }
        ```
    * POST /{table1},{table2}/hdel 删除
    * POST /{table1},{table2}/mdel 软删除
    * POST /{table1},{table2}/get   查询
