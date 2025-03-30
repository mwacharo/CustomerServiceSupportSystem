import{A as U}from"./AppLayout-DhE9y64o.js";import{_ as A}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{i as a,o as C,c as L,w as t,b as e,f as d,D as y,E as D}from"./app-BTFf-cCJ.js";const B={components:{AppLayout:U},data(){return{selectedReportType:null,selectedStatus:null,startDate:null,endDate:null,menu1:!1,menu2:!1,reportTypes:["Loan Summary","Repayment Schedule","Borrower Activity"],statusTypes:["All","Approved","Pending","Rejected"],reportData:[],headers:[{title:"Report Date",value:"date"},{title:"Report Type",value:"type"},{title:"Status",value:"status"},{title:"Details",value:"details"}]}},methods:{generateReport(){},downloadReport(){}}};function E(I,o,N,P,l,c){const R=a("v-spacer"),b=a("v-divider"),u=a("v-text-field"),s=a("v-col"),r=a("v-icon"),m=a("v-btn"),_=a("v-row"),f=a("v-select"),v=a("v-date-picker"),V=a("v-menu"),w=a("v-data-table"),k=a("v-container"),x=a("v-card"),S=a("v-main"),T=a("v-app"),g=a("AppLayout");return C(),L(g,null,{default:t(()=>[e(T,null,{default:t(()=>[e(S,null,{default:t(()=>[e(R),e(b),e(_,{class:"mt-4"},{default:t(()=>[e(s,null,{default:t(()=>[e(u,{label:"Search ","prepend-inner-icon":"mdi-magnify",clearable:"",outlined:""})]),_:1}),e(s,{cols:"auto"},{default:t(()=>[e(m,{icon:""},{default:t(()=>[e(r,null,{default:t(()=>[d(" mdi-filter-variant")]),_:1})]),_:1})]),_:1})]),_:1}),e(x,{class:"my-card"},{default:t(()=>[e(k,null,{default:t(()=>[e(_,null,{default:t(()=>[e(s,{cols:"12",md:"4"},{default:t(()=>[e(f,{modelValue:l.selectedReportType,"onUpdate:modelValue":o[0]||(o[0]=n=>l.selectedReportType=n),items:l.reportTypes,label:"Report Type"},null,8,["modelValue","items"])]),_:1}),e(s,{cols:"12",md:"4"},{default:t(()=>[e(V,{modelValue:l.menu1,"onUpdate:modelValue":o[4]||(o[4]=n=>l.menu1=n),"close-on-content-click":!1,transition:"scale-transition","offset-y":"","min-width":"290px"},{activator:t(({on:n,attrs:p})=>[e(u,y({modelValue:l.startDate,"onUpdate:modelValue":o[1]||(o[1]=i=>l.startDate=i),label:"Start Date","prepend-icon":"mdi-calendar",readonly:""},p,D(n)),null,16,["modelValue"])]),default:t(()=>[e(v,{modelValue:l.startDate,"onUpdate:modelValue":o[2]||(o[2]=n=>l.startDate=n),onInput:o[3]||(o[3]=n=>l.menu1=!1)},null,8,["modelValue"])]),_:1},8,["modelValue"])]),_:1}),e(s,{cols:"12",md:"4"},{default:t(()=>[e(V,{modelValue:l.menu2,"onUpdate:modelValue":o[8]||(o[8]=n=>l.menu2=n),"close-on-content-click":!1,transition:"scale-transition","offset-y":"","min-width":"290px"},{activator:t(({on:n,attrs:p})=>[e(u,y({modelValue:l.endDate,"onUpdate:modelValue":o[5]||(o[5]=i=>l.endDate=i),label:"End Date","prepend-icon":"mdi-calendar",readonly:""},p,D(n)),null,16,["modelValue"])]),default:t(()=>[e(v,{modelValue:l.endDate,"onUpdate:modelValue":o[6]||(o[6]=n=>l.endDate=n),onInput:o[7]||(o[7]=n=>l.menu2=!1)},null,8,["modelValue"])]),_:1},8,["modelValue"])]),_:1}),e(s,{cols:"12",md:"4"},{default:t(()=>[e(f,{modelValue:l.selectedStatus,"onUpdate:modelValue":o[9]||(o[9]=n=>l.selectedStatus=n),items:l.statusTypes,label:"Status"},null,8,["modelValue","items"])]),_:1}),e(s,{cols:"12",class:"text-right"},{default:t(()=>[e(m,{color:"primary",onClick:c.generateReport},{default:t(()=>[e(r,{left:""},{default:t(()=>[d("mdi-magnify")]),_:1}),d(" Search ")]),_:1},8,["onClick"]),e(m,{color:"secondary",onClick:c.downloadReport},{default:t(()=>[e(r,{left:""},{default:t(()=>[d("mdi-download")]),_:1}),d(" Download ")]),_:1},8,["onClick"])]),_:1})]),_:1}),e(w,{headers:l.headers,items:l.reportData,"items-per-page":10,class:"elevation-1"},null,8,["headers","items"])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}const z=A(B,[["render",E]]);export{z as default};
