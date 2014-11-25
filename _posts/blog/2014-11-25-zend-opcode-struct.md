---
layout: blog_contents
title: Zend Opcode数据结构
categories: blog
---

Zend opcode 相关数据结构定义在`/php-src/Zend/zend_compile.h`中

Zend opcode基本结构是`zend_op`,定义如下  

```c
typedef struct _zend_op zend_op;
......
struct _zend_op {
	opcode_handler_t handler;
	znode_op op1;
	znode_op op2;
	znode_op result;
	zend_ulong extended_value;
	uint lineno;
	zend_uchar opcode;
	zend_uchar op1_type;
	zend_uchar op2_type;
	zend_uchar result_type;
};
```
在引擎内部的通常使用`opline`这个全局变量来访问,`opcode`使用`/php-src/Zend/zend_compile.c`中的`zend_emit_op`函数创建  

`znode_op`定义如下:

```c
typedef union _znode_op {
	uint32_t      constant;
	uint32_t      var;
	uint32_t      num;
	uint32_t      opline_num; /*  Needs to be signed */
	zend_op       *jmp_addr;
	zval          *zv;
} znode_op;
```

函数内部的opcode的结构为`zend_op_array`,定义如下:  

```c
typedef struct _zend_op_array zend_op_array;
......
struct _zend_op_array {
	/* Common elements */
	zend_uchar type;
	uint32_t fn_flags;
	zend_string *function_name;
	zend_class_entry *scope;
	zend_function *prototype;
	uint32_t num_args;
	uint32_t required_num_args;
	zend_arg_info *arg_info;
	/* END of common elements */

	uint32_t *refcount;

	uint32_t this_var;

	uint32_t last;
	zend_op *opcodes;

	int last_var;
	uint32_t T;
	zend_string **vars;

	int last_brk_cont;
	int last_try_catch;
	zend_brk_cont_element *brk_cont_array;
	zend_try_catch_element *try_catch_array;

	/* static variables support */
	HashTable *static_variables;

	zend_string *filename;
	uint32_t line_start;
	uint32_t line_end;
	zend_string *doc_comment;
	uint32_t early_binding; /* the linked list of delayed declarations */

	int last_literal;
	zval *literals;

	int  last_cache_slot;
	void **run_time_cache;

	void *reserved[ZEND_MAX_RESERVED_RESOURCES];
};
```

zval 作为 php zend常用的数据结构，它定义在`/php-src/Zend/zend_types.h`中

```c
typedef struct _zval_struct     zval;
.......
struct _zval_struct {
	zend_value        value;			/* value */
	union {
		struct {
			ZEND_ENDIAN_LOHI_4(
				zend_uchar    type,			/* active type */
				zend_uchar    type_flags,
				zend_uchar    const_flags,
				zend_uchar    reserved)	    /* various IS_VAR flags */
		} v;
		uint32_t type_info;
	} u1;
	union {
		uint32_t     var_flags;
		uint32_t     next;                 /* hash collision chain */
		uint32_t     cache_slot;           /* literal cache slot */
		uint32_t     lineno;               /* line number (for ast nodes) */
	} u2;
};
```
`zend_value`定义如下,位于文件`/php-src/Zend/zend_types.h`中：  
```c
typedef union _zend_value {
	zend_long         lval;				/* long value */
	double            dval;				/* double value */
	zend_refcounted  *counted;
	zend_string      *str;
	zend_array       *arr;
	zend_object      *obj;
	zend_resource    *res;
	zend_reference   *ref;
	zend_ast_ref     *ast;
	zval             *zv;
	void             *ptr;
	zend_class_entry *ce;
	zend_function    *func;
} zend_value;
```
