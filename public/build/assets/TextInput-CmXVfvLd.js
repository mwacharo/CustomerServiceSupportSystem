import{l,A as i,o,d as u,a as c,t as d,r as m,E as p}from"./app-BC52LmwV.js";const f={class:"text-sm text-red-600"},v={__name:"InputError",props:{message:String},setup(t){return(s,e)=>l((o(),u("div",null,[c("p",f,d(t.message),1)],512)),[[i,t.message]])}},_=["value"],h={__name:"TextInput",props:{modelValue:String},emits:["update:modelValue"],setup(t,{expose:s}){const e=m(null);return p(()=>{e.value.hasAttribute("autofocus")&&e.value.focus()}),s({focus:()=>e.value.focus()}),(r,a)=>(o(),u("input",{ref_key:"input",ref:e,class:"border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm",value:t.modelValue,onInput:a[0]||(a[0]=n=>r.$emit("update:modelValue",n.target.value))},null,40,_))}};export{h as _,v as a};
